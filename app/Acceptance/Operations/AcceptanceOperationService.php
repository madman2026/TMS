<?php

namespace App\Acceptance\Operations;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Modules\TargetModuleException;
use App\Acceptance\Operations\Data\CatalogOperationData;
use App\Acceptance\Operations\Data\ModuleChangeData;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Operations\Data\RunOperationData;
use App\Acceptance\Operations\Data\TargetModuleValidationData;
use App\Acceptance\Prerequisites\Data\PrerequisiteOperationData;
use App\Acceptance\Prerequisites\PrerequisiteException;
use App\Acceptance\Targets\Data\TargetResourceLifecycleData;
use App\Exceptions\AcceptanceCatalogException;
use App\Exceptions\AcceptanceRegistryException;
use App\TestStatusEnum;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Throwable;

/** Authoritative request, safe-result, trace and logging boundary for every client. */
final class AcceptanceOperationService
{
    private const REJECTED_CODES = [
        'operation_not_found', 'operation_request_invalid', 'acceptance_app_not_found',
        'acceptance_profile_not_found', 'acceptance_selector_invalid',
        'acceptance_selector_not_found', 'acceptance_catalog_limit_exceeded', 'acceptance_variant_not_executable',
        'target_module_name_invalid', 'acceptance_hierarchy_key_invalid', 'target_module_not_found',
        'target_module_exists', 'target_module_path_collision', 'acceptance_source_mapping_invalid',
        'acceptance_source_mapping_duplicate',
        'prerequisite_request_not_found', 'input_required', 'approval_required', 'input_invalid',
        'secret_literal_forbidden', 'approval_stale', 'request_expired', 'invalid_transition',
        'prerequisite_request_mismatch', 'unsafe_target', 'target_not_ready', 'resource_unavailable',
    ];

    private const ADMIN_CODES = [
        'operation_registry_invalid', 'operation_registry_duplicate', 'acceptance_registry_invalid',
        'acceptance_registry_duplicate', 'acceptance_catalog_invalid',
        'acceptance_hierarchy_invalid', 'acceptance_hierarchy_duplicate',
        'target_module_path_invalid',
        'prerequisite_schema_invalid', 'schema_changed',
        'unsafe_target', 'resource_unavailable',
    ];

    public function __construct(private readonly AcceptanceOperationRegistry $registry) {}

    public function execute(OperationRequest $request): OperationResult
    {
        $validCorrelation = $request->correlationId === null || OperationResult::isUuid($request->correlationId);
        $correlationId = $validCorrelation && $request->correlationId !== null
            ? $request->correlationId : (string) Str::uuid();
        $operation = $this->registry->has($request->operation) ? $request->operation : null;
        $operationId = in_array($operation, [
            'acceptance.run', 'acceptance.app.create', 'acceptance.component.create', 'acceptance.scenarios.import',
            'acceptance.prerequisite.request.prepare', 'acceptance.prerequisite.input.submit',
            'acceptance.prerequisite.approval.grant', 'acceptance.prerequisite.request.cancel',
        ], true) ? (string) Str::uuid() : null;
        $errors = [];
        if ($request->version !== 2) {
            $errors['version'] = ['operation_request_invalid'];
        }
        if (! $validCorrelation) {
            $errors['correlationId'] = ['operation_request_invalid'];
        }
        if ($errors !== []) {
            return $this->finish($operation, 'rejected', 'operation_request_invalid', $correlationId, $operationId, errors: $errors);
        }
        if ($operation === null) {
            return $this->finish(null, 'rejected', 'operation_not_found', $correlationId, null);
        }
        $errors = $this->parameterErrors($request);
        if ($errors !== []) {
            return $this->finish($operation, 'rejected', 'operation_request_invalid', $correlationId, $operationId, errors: $errors);
        }
        $identityContext = $this->safeContext($request);

        try {
            $handler = $this->registry->resolve($operation);
        } catch (Throwable) {
            return $this->finish($operation, 'failed', 'operation_registry_invalid', $correlationId, $operationId,
                identityContext: $identityContext);
        }
        try {
            $result = $handler->handle($request, $correlationId, $operationId);
            $data = $result->data;
            if (! $this->matchesOperationData($operation, $data)) {
                throw new \UnexpectedValueException('operation_result_invalid');
            }
            if (($result->status === 'succeeded' && $data === null)
                || ($result->status === 'rejected' && $data !== null
                    && ! $data instanceof TargetResourceLifecycleData)
                || (($data instanceof CatalogOperationData || $data instanceof ModuleChangeData
                    || $data instanceof TargetModuleValidationData || $data instanceof PrerequisiteOperationData)
                    && $result->status !== 'succeeded')
                || ($data instanceof PrerequisiteOperationData
                    && ! $this->matchesPrerequisiteData($data, $request, $correlationId, $operationId))
                || ($data instanceof RunOperationData && ($data->errorCode !== $result->errorCode
                    || ! $this->matchesRequestedIdentity($data, $request)
                    || ! $this->matchesLifecycleData($data->resources, $request, $correlationId, $operationId)))
                || ($data instanceof TargetResourceLifecycleData
                    && (($data->primaryErrorCode ?? $data->cleanupErrorCode) !== $result->errorCode
                        || ! $this->matchesLifecycleData($data, $request, $correlationId, $operationId)))) {
                throw new \UnexpectedValueException('operation_result_invalid');
            }
            $status = $data instanceof RunOperationData
                ? ($data->testStatus === TestStatusEnum::FINISHED && $data->resources->status === 'succeeded'
                    ? 'succeeded' : 'failed')
                : $result->status;

            // Rebuild identity, classification and traces; a handler cannot replace them.
            return $this->finish($operation, $status, $result->errorCode, $correlationId, $operationId,
                $data, $result->errors, identityContext: $identityContext);
        } catch (AcceptanceCatalogException $exception) {
            return $this->finish($operation, $exception->rejected() ? 'rejected' : 'failed', $exception->errorCode,
                $correlationId, $operationId, exceptionClass: $exception::class, identityContext: $identityContext);
        } catch (AcceptanceRegistryException $exception) {
            $code = in_array($exception->errorCode, ['acceptance_registry_invalid', 'acceptance_registry_duplicate'], true)
                ? $exception->errorCode : $this->fallback($operation);

            return $this->finish($operation, 'failed', $code, $correlationId, $operationId,
                exceptionClass: $exception::class, identityContext: $identityContext);
        } catch (TargetModuleException $exception) {
            $code = in_array($exception->errorCode, OperationResult::ERROR_CODES, true)
                ? $exception->errorCode : $this->fallback($operation);
            $status = in_array($code, [...self::REJECTED_CODES, 'target_module_path_invalid'], true)
                ? 'rejected' : 'failed';

            return $this->finish($operation, $status, $code, $correlationId, $operationId,
                exceptionClass: $exception::class, identityContext: $identityContext);
        } catch (PrerequisiteException $exception) {
            $code = $exception->errorCode;
            $status = $code === 'prerequisite_persistence_failed' || $code === 'prerequisite_schema_invalid'
                ? 'failed' : 'rejected';

            return $this->finish($operation, $status, $code, $correlationId, $operationId,
                exceptionClass: $exception::class, identityContext: $identityContext);
        } catch (Throwable) {
            return $this->finish($operation, 'failed', $this->fallback($operation), $correlationId, $operationId,
                identityContext: $identityContext);
        }
    }

    /** Shape validation deliberately precedes factories, not existing domain lookup rules. */
    private function parameterErrors(OperationRequest $request): array
    {
        $run = $request->operation === 'acceptance.run';
        $completeHierarchy = $run || $request->operation === 'acceptance.prerequisite.request.prepare';
        $lists = ['app', 'component', 'suite', 'scenario', 'variant', 'capability', 'tag', 'disposition', 'evidence_mode'];
        $allowed = match ($request->operation) {
            'acceptance.run' => ['app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key',
                'profile_id', 'browser', 'headed', 'timeout_ms', 'slow_mo_ms', 'request_id'],
            'acceptance.app.create' => ['module_name', 'app_key', 'dry_run'],
            'acceptance.app.validate' => ['module_name'],
            'acceptance.component.create' => ['module_name', 'component_key', 'dry_run'],
            'acceptance.scenarios.import' => ['module_name', 'mappings', 'dry_run'],
            'acceptance.prerequisite.request.prepare' => [
                'request_id', 'app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key', 'profile_id',
            ],
            'acceptance.prerequisite.input.submit' => ['request_id', 'expected_lock_version', 'inputs'],
            'acceptance.prerequisite.approval.grant' => ['request_id', 'expected_lock_version', 'scope'],
            'acceptance.prerequisite.request.cancel' => ['request_id', 'expected_lock_version'],
            default => [...$lists, 'limit'],
        };
        $errors = [];
        foreach ($request->parameters as $field => $value) {
            if (! in_array($field, $allowed, true)) {
                $errors['parameters'] = ['operation_request_invalid'];

                continue;
            }
            $valid = match ($field) {
                'app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key' => is_string($value)
                    && (! $completeHierarchy || (strlen($value) <= 64
                        && preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $value) === 1)),
                'profile_id', 'limit' => is_int($value) || is_string($value),
                'browser' => $value === null || is_string($value),
                'headed' => is_bool($value),
                'timeout_ms', 'slow_mo_ms' => $value === null || is_int($value) || is_string($value),
                'module_name' => is_string($value),
                'dry_run' => is_bool($value),
                'mappings' => is_array($value) && array_is_list($value)
                    && count(array_filter($value, fn (mixed $entry): bool => $entry instanceof SourceCaseMapping)) === count($value),
                'request_id' => is_string($value) && OperationResult::isUuid($value),
                'expected_lock_version' => is_int($value) && $value >= 0,
                'inputs' => $this->validInputMap($value),
                'scope' => is_string($value) && strlen($value) <= 64
                    && preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $value) === 1,
                default => is_array($value) && array_is_list($value)
                    && count(array_filter($value, 'is_string')) === count($value),
            };
            if (! $valid) {
                $errors[$field] = ['operation_request_invalid'];
            }
        }
        if ($run) {
            foreach (['app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key', 'profile_id'] as $field) {
                if (! array_key_exists($field, $request->parameters)) {
                    $errors[$field] = ['operation_request_invalid'];
                }
            }
        }
        $required = match ($request->operation) {
            'acceptance.app.create' => ['module_name', 'app_key'],
            'acceptance.app.validate' => ['module_name'],
            'acceptance.component.create' => ['module_name', 'component_key'],
            'acceptance.scenarios.import' => ['module_name', 'mappings'],
            'acceptance.prerequisite.input.submit' => ['request_id', 'expected_lock_version', 'inputs'],
            'acceptance.prerequisite.approval.grant' => ['request_id', 'expected_lock_version', 'scope'],
            'acceptance.prerequisite.request.cancel' => ['request_id', 'expected_lock_version'],
            default => [],
        };
        foreach ($required as $field) {
            if (! array_key_exists($field, $request->parameters)) {
                $errors[$field] = ['operation_request_invalid'];
            }
        }
        if ($request->operation === 'acceptance.prerequisite.request.prepare') {
            $keys = array_keys($request->parameters);
            sort($keys, SORT_STRING);
            $byRequest = ['request_id'];
            $byIdentity = ['app_key', 'component_key', 'profile_id', 'scenario_key', 'suite_key', 'variant_key'];
            if ($keys !== $byRequest && $keys !== $byIdentity) {
                $errors['parameters'] = ['operation_request_invalid'];
            }
        }

        return $errors;
    }

    private function validInputMap(mixed $inputs): bool
    {
        if (! is_array($inputs) || $inputs === [] || array_is_list($inputs)) {
            return false;
        }
        foreach ($inputs as $key => $payload) {
            if (! is_string($key) || strlen($key) > 64
                || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1
                || ! is_array($payload) || count($payload) !== 2
                || ! array_key_exists('source', $payload) || ! array_key_exists('value', $payload)
                || ! is_string($payload['source'])) {
                return false;
            }
            $value = $payload['value'];
            if (is_string($value) || is_int($value) || is_bool($value)) {
                continue;
            }
            if (! is_array($value) || ! array_is_list($value)
                || count(array_filter($value, 'is_string')) !== count($value)) {
                return false;
            }
        }

        return true;
    }

    private function fallback(string $operation): string
    {
        return match ($operation) {
            'acceptance.run' => 'acceptance_command_failed',
            'acceptance.app.validate' => 'target_module_validation_failed',
            'acceptance.app.create', 'acceptance.component.create', 'acceptance.scenarios.import' => 'target_module_generation_failed',
            'acceptance.prerequisite.request.prepare', 'acceptance.prerequisite.input.submit',
            'acceptance.prerequisite.approval.grant', 'acceptance.prerequisite.request.cancel' => 'prerequisite_persistence_failed',
            default => 'acceptance_catalog_failed',
        };
    }

    private function matchesRequestedIdentity(RunOperationData $data, OperationRequest $request): bool
    {
        return $data->appKey === $request->parameters['app_key']
            && $data->componentKey === $request->parameters['component_key']
            && $data->suiteKey === $request->parameters['suite_key']
            && $data->scenarioKey === $request->parameters['scenario_key']
            && $data->variantKey === $request->parameters['variant_key'];
    }

    private function matchesLifecycleData(
        TargetResourceLifecycleData $data,
        OperationRequest $request,
        string $correlationId,
        ?string $operationId,
    ): bool {
        return $operationId !== null
            && $data->lifecycleId === $operationId
            && $data->correlationId === $correlationId
            && $data->prerequisiteRequestId === ($request->parameters['request_id'] ?? null)
            && $data->appKey === $request->parameters['app_key']
            && $data->componentKey === $request->parameters['component_key']
            && $data->suiteKey === $request->parameters['suite_key']
            && $data->scenarioKey === $request->parameters['scenario_key']
            && $data->variantKey === $request->parameters['variant_key']
            && $data->profileId === (int) $request->parameters['profile_id'];
    }

    private function matchesPrerequisiteData(
        PrerequisiteOperationData $data,
        OperationRequest $request,
        string $correlationId,
        ?string $operationId,
    ): bool {
        if ($data->correlationId !== $correlationId) {
            return false;
        }
        $requestId = $request->parameters['request_id'] ?? $operationId;
        if ($requestId === null || $data->requestId !== $requestId) {
            return false;
        }
        if ($request->operation !== 'acceptance.prerequisite.request.prepare'
            || isset($request->parameters['request_id'])) {
            return true;
        }

        return $data->appKey === $request->parameters['app_key']
            && $data->componentKey === $request->parameters['component_key']
            && $data->suiteKey === $request->parameters['suite_key']
            && $data->scenarioKey === $request->parameters['scenario_key']
            && $data->variantKey === $request->parameters['variant_key']
            && $data->profileId === (int) $request->parameters['profile_id'];
    }

    /** Include only validated language-neutral identifiers and flags in logs. */
    private function safeContext(OperationRequest $request): array
    {
        $context = [];
        foreach (['app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key'] as $field) {
            $value = $request->parameters[$field] ?? null;
            if ($value === null) {
                continue;
            }
            if (strlen($value) > 64 || ! preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $value)) {
                return [];
            }
            $context[$field] = $value;
        }
        $moduleName = $request->parameters['module_name'] ?? null;
        if ($moduleName !== null) {
            if (! is_string($moduleName) || ! preg_match('/^[A-Z][A-Za-z0-9]{0,63}$/D', $moduleName)) {
                return [];
            }
            $context['module_name'] = $moduleName;
        }
        if (isset($request->parameters['dry_run']) && is_bool($request->parameters['dry_run'])) {
            $context['dry_run'] = $request->parameters['dry_run'];
        } elseif (in_array($request->operation, [
            'acceptance.app.create', 'acceptance.component.create', 'acceptance.scenarios.import',
        ], true)) {
            $context['dry_run'] = true;
        }

        return $context;
    }

    private function matchesOperationData(string $operation, mixed $data): bool
    {
        if ($data === null) {
            return true;
        }

        return match ($operation) {
            'acceptance.run' => $data instanceof RunOperationData || $data instanceof TargetResourceLifecycleData,
            'acceptance.list', 'acceptance.plan' => $data instanceof CatalogOperationData,
            'acceptance.app.create', 'acceptance.component.create', 'acceptance.scenarios.import' => $data instanceof ModuleChangeData,
            'acceptance.app.validate' => $data instanceof TargetModuleValidationData,
            'acceptance.prerequisite.request.prepare', 'acceptance.prerequisite.input.submit',
            'acceptance.prerequisite.approval.grant', 'acceptance.prerequisite.request.cancel' => $data instanceof PrerequisiteOperationData,
            default => false,
        };
    }

    private function finish(
        ?string $operation,
        string $status,
        ?string $code,
        string $correlationId,
        ?string $operationId,
        CatalogOperationData|RunOperationData|ModuleChangeData|TargetModuleValidationData|PrerequisiteOperationData|TargetResourceLifecycleData|null $data = null,
        array $errors = [],
        ?string $exceptionClass = null,
        array $identityContext = [],
    ): OperationResult {
        [$retryable, $permanent, $admin] = match (true) {
            $status === 'succeeded' => [null, null, false],
            in_array($code, ['unsafe_target', 'resource_unavailable'], true) => [false, true, true],
            in_array($code, self::REJECTED_CODES, true),
            in_array($code, ['acceptance_configuration_invalid', 'acceptance_scenario_failed', 'acceptance_step_failed'], true) => [false, true, false],
            in_array($code, self::ADMIN_CODES, true) => [false, true, true],
            $code === 'acceptance_catalog_changed' => [true, false, false],
            $code === 'conflict' => [true, false, false],
            $code === 'target_not_ready' => [true, false, false],
            $code === 'fixture_setup_failed' || $code === 'cleanup_failed' => [true, false, true],
            $code === 'oracle_failed' => [false, true, false],
            in_array($code, ['acceptance_browser_start_failed', 'acceptance_result_persistence_failed'], true) => [true, false, true],
            default => [null, null, true],
        };
        $result = new OperationResult($operation, $status, $code, $correlationId, $operationId,
            $retryable, $permanent, $admin, $errors, $data);
        $context = [
            'operation' => $operation, 'error_code' => $code, 'correlation_id' => $correlationId,
            'operation_id' => $operationId, 'retryable' => $retryable, 'permanent' => $permanent,
            'admin_action_required' => $admin,
            ...$identityContext,
        ];
        if ($data instanceof RunOperationData) {
            $context['test_id'] = $data->testId;
            $context += $this->resourceContext($data->resources);
        } elseif ($data instanceof TargetResourceLifecycleData) {
            $context += $this->resourceContext($data);
        } elseif ($data instanceof ModuleChangeData) {
            $context['module_name'] = $data->moduleName;
            if ($data->appKey !== null) {
                $context['app_key'] = $data->appKey;
            }
            if ($data->componentKey !== null) {
                $context['component_key'] = $data->componentKey;
            }
            $context['dry_run'] = $data->dryRun;
            $context['change_count'] = count($data->changes);
        }
        if (in_array($exceptionClass, [AcceptanceCatalogException::class, AcceptanceRegistryException::class,
            AcceptanceExecutionException::class, TargetModuleException::class, PrerequisiteException::class], true)) {
            $context['exception_class'] = $exceptionClass;
        }
        if ($status !== 'succeeded') {
            Log::log($status === 'rejected' ? 'warning' : 'error', 'tms.acceptance.operation.failed', $context);
        } elseif (in_array($operation, [
            'acceptance.run', 'acceptance.app.create', 'acceptance.component.create', 'acceptance.scenarios.import',
        ], true)) {
            Log::info('tms.acceptance.operation.completed', $context);
        }

        return $result;
    }

    /** Include only opaque hashes and semantic lifecycle fields in operation logs. */
    private function resourceContext(TargetResourceLifecycleData $data): array
    {
        return array_filter([
            'lifecycle_id' => $data->lifecycleId,
            'prerequisite_request_id' => $data->prerequisiteRequestId,
            'resource_status' => $data->status,
            'resource_stage' => $data->stage,
            'primary_error_code' => $data->primaryErrorCode,
            'cleanup_error_code' => $data->cleanupErrorCode,
            'resources' => array_map(
                fn ($reference): array => [
                    'type' => $reference->type,
                    'reference_hash' => $reference->referenceHash,
                ],
                $data->references,
            ),
        ], fn (mixed $value): bool => $value !== null);
    }
}
