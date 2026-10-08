<?php

namespace App\Acceptance\Operations;

use App\Acceptance\Operations\Data\CatalogOperationData;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Operations\Data\RunOperationData;
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
    ];

    private const ADMIN_CODES = [
        'operation_registry_invalid', 'operation_registry_duplicate', 'acceptance_registry_invalid',
        'acceptance_registry_duplicate', 'acceptance_catalog_invalid',
        'acceptance_hierarchy_invalid', 'acceptance_hierarchy_duplicate',
    ];

    public function __construct(private readonly AcceptanceOperationRegistry $registry) {}

    public function execute(OperationRequest $request): OperationResult
    {
        $validCorrelation = $request->correlationId === null || OperationResult::isUuid($request->correlationId);
        $correlationId = $validCorrelation && $request->correlationId !== null
            ? $request->correlationId : (string) Str::uuid();
        $operation = $this->registry->has($request->operation) ? $request->operation : null;
        $operationId = $operation === 'acceptance.run' ? (string) Str::uuid() : null;
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
        $identityContext = $operation === 'acceptance.run' ? $this->identityContext($request) : [];

        try {
            $handler = $this->registry->resolve($operation);
        } catch (Throwable) {
            return $this->finish($operation, 'failed', 'operation_registry_invalid', $correlationId, $operationId,
                identityContext: $identityContext);
        }
        try {
            $result = $handler->handle($request, $correlationId, $operationId);
            $data = $result->data;
            if ($data !== null && (($operation === 'acceptance.run' && ! $data instanceof RunOperationData)
                || ($operation !== 'acceptance.run' && ! $data instanceof CatalogOperationData))) {
                throw new \UnexpectedValueException('operation_result_invalid');
            }
            if (($result->status === 'succeeded' && $data === null)
                || ($result->status === 'rejected' && $data !== null)
                || ($data instanceof CatalogOperationData && $result->status !== 'succeeded')
                || ($data instanceof RunOperationData && ($data->errorCode !== $result->errorCode
                    || ! $this->matchesRequestedIdentity($data, $request)))) {
                throw new \UnexpectedValueException('operation_result_invalid');
            }
            $status = $data instanceof RunOperationData
                ? ($data->testStatus === TestStatusEnum::FINISHED ? 'succeeded' : 'failed')
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
        } catch (Throwable) {
            return $this->finish($operation, 'failed', $this->fallback($operation), $correlationId, $operationId,
                identityContext: $identityContext);
        }
    }

    /** Shape validation deliberately precedes factories, not existing domain lookup rules. */
    private function parameterErrors(OperationRequest $request): array
    {
        $run = $request->operation === 'acceptance.run';
        $lists = ['app', 'component', 'suite', 'scenario', 'variant', 'capability', 'tag', 'disposition', 'evidence_mode'];
        $allowed = $run
            ? ['app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key',
                'profile_id', 'browser', 'headed', 'timeout_ms', 'slow_mo_ms']
            : [...$lists, 'limit'];
        $errors = [];
        foreach ($request->parameters as $field => $value) {
            if (! in_array($field, $allowed, true)) {
                $errors['parameters'] = ['operation_request_invalid'];

                continue;
            }
            $valid = match ($field) {
                'app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key' => is_string($value)
                    && strlen($value) <= 64
                    && preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $value) === 1,
                'profile_id', 'limit' => is_int($value) || is_string($value),
                'browser' => $value === null || is_string($value),
                'headed' => is_bool($value),
                'timeout_ms', 'slow_mo_ms' => $value === null || is_int($value) || is_string($value),
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

        return $errors;
    }

    private function fallback(string $operation): string
    {
        return $operation === 'acceptance.run' ? 'acceptance_command_failed' : 'acceptance_catalog_failed';
    }

    private function matchesRequestedIdentity(RunOperationData $data, OperationRequest $request): bool
    {
        return $data->appKey === $request->parameters['app_key']
            && $data->componentKey === $request->parameters['component_key']
            && $data->suiteKey === $request->parameters['suite_key']
            && $data->scenarioKey === $request->parameters['scenario_key']
            && $data->variantKey === $request->parameters['variant_key'];
    }

    /** Include only fully validated language-neutral identity values in logs. */
    private function identityContext(OperationRequest $request): array
    {
        $context = [];
        foreach (['app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key'] as $field) {
            $value = $request->parameters[$field];
            if (strlen($value) > 64 || ! preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $value)) {
                return [];
            }
            $context[$field] = $value;
        }

        return $context;
    }

    private function finish(
        ?string $operation,
        string $status,
        ?string $code,
        string $correlationId,
        ?string $operationId,
        CatalogOperationData|RunOperationData|null $data = null,
        array $errors = [],
        ?string $exceptionClass = null,
        array $identityContext = [],
    ): OperationResult {
        [$retryable, $permanent, $admin] = match (true) {
            $status === 'succeeded' => [null, null, false],
            in_array($code, self::REJECTED_CODES, true),
            in_array($code, ['acceptance_configuration_invalid', 'acceptance_scenario_failed', 'acceptance_step_failed'], true) => [false, true, false],
            in_array($code, self::ADMIN_CODES, true) => [false, true, true],
            $code === 'acceptance_catalog_changed' => [true, false, false],
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
        }
        if (in_array($exceptionClass, [AcceptanceCatalogException::class, AcceptanceRegistryException::class,
            AcceptanceExecutionException::class], true)) {
            $context['exception_class'] = $exceptionClass;
        }
        if ($status !== 'succeeded') {
            Log::log($status === 'rejected' ? 'warning' : 'error', 'tms.acceptance.operation.failed', $context);
        } elseif ($operation === 'acceptance.run') {
            Log::info('tms.acceptance.operation.completed', $context);
        }

        return $result;
    }
}
