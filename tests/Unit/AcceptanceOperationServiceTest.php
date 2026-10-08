<?php

namespace Tests\Unit;

use App\Acceptance\Operations\AcceptanceOperationRegistry;
use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\CatalogOperationData;
use App\Acceptance\Operations\Data\FileChange;
use App\Acceptance\Operations\Data\ModuleChangeData;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Operations\Data\RunOperationData;
use App\Acceptance\Operations\Data\TargetModuleValidationData;
use App\Acceptance\Operations\Data\ValidationIssue;
use App\Acceptance\Prerequisites\Data\ApprovalFact;
use App\Acceptance\Prerequisites\Data\ApprovalRequirement;
use App\Acceptance\Prerequisites\Data\InputRequirement;
use App\Acceptance\Prerequisites\Data\OperatorContext;
use App\Acceptance\Prerequisites\Data\PrerequisiteOperationData;
use App\Acceptance\Prerequisites\Data\PrerequisiteSchema;
use App\Data\AcceptancePlan;
use App\Data\AcceptancePlanItem;
use App\Data\AcceptanceSelector;
use App\Exceptions\AcceptanceCatalogException;
use App\Exceptions\AcceptanceRegistryException;
use App\TestStatusEnum;
use Closure;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;
use Mockery;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

class AcceptanceOperationServiceTest extends TestCase
{
    private const UUID = 'dcb1cf9d-207c-4a44-963b-000000000009';

    protected function setUp(): void
    {
        parent::setUp();
        Log::swap(Mockery::spy(LogManager::class));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Log::clearResolvedInstances();
        parent::tearDown();
    }

    public function test_version_two_is_the_only_accepted_request_and_result_contract(): void
    {
        $calls = 0;
        $registry = new AcceptanceOperationRegistry;
        $registry->register('acceptance.run', function () use (&$calls): AcceptanceOperationHandler {
            $calls++;

            return $this->successfulHandler();
        });
        $service = new AcceptanceOperationService($registry);

        $old = $service->execute(new OperationRequest('acceptance.run', $this->parameters(), 1));
        $this->assertSame('rejected', $old->status);
        $this->assertSame(['version' => ['operation_request_invalid']], $old->errors);
        $this->assertSame(2, $old->version);
        $this->assertSame(0, $calls);

        $current = $service->execute(new OperationRequest('acceptance.run', $this->parameters()));
        $this->assertSame('succeeded', $current->status);
        $this->assertSame(2, $current->version);
        $this->assertSame(1, $calls);
    }

    public function test_run_requires_every_tuple_key_and_rejects_invalid_identity_before_factory_resolution(): void
    {
        $calls = 0;
        $registry = new AcceptanceOperationRegistry;
        $registry->register('acceptance.run', function () use (&$calls): AcceptanceOperationHandler {
            $calls++;

            return $this->successfulHandler();
        });
        $service = new AcceptanceOperationService($registry);

        $missing = $service->execute(new OperationRequest('acceptance.run', [
            'app_key' => 'app-a',
            'scenario_key' => 'scenario-a',
            'profile_id' => 1,
        ]));
        $this->assertSame([
            'component_key', 'suite_key', 'variant_key',
        ], array_keys($missing->errors));

        $invalid = $service->execute(new OperationRequest('acceptance.run', [
            ...$this->parameters(),
            'variant_key' => 'Invalid sensitive value',
        ]));
        $this->assertSame(['variant_key' => ['operation_request_invalid']], $invalid->errors);
        $this->assertSame(0, $calls);
    }

    public function test_successful_run_rebuilds_result_and_logs_the_complete_safe_identity(): void
    {
        $service = $this->service($this->successfulHandler());

        $result = $service->execute(new OperationRequest(
            'acceptance.run',
            $this->parameters(),
            correlationId: self::UUID,
        ));

        $this->assertSame('succeeded', $result->status);
        $this->assertInstanceOf(RunOperationData::class, $result->data);
        $this->assertSame('component-a', $result->data->componentKey);
        $this->assertSame('suite-a', $result->data->suiteKey);
        $this->assertSame('variant-a', $result->data->variantKey);
        Log::shouldHaveReceived('info')->once()->with(
            'tms.acceptance.operation.completed',
            Mockery::on(fn (array $context): bool => $context['app_key'] === 'app-a'
                && $context['component_key'] === 'component-a'
                && $context['suite_key'] === 'suite-a'
                && $context['scenario_key'] === 'scenario-a'
                && $context['variant_key'] === 'variant-a'
                && $context['test_id'] === 10),
        );
    }

    public function test_hierarchy_errors_are_safe_admin_failures(): void
    {
        $handler = new class implements AcceptanceOperationHandler
        {
            public function name(): string
            {
                return 'acceptance.run';
            }

            public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
            {
                throw AcceptanceCatalogException::because('acceptance_hierarchy_invalid');
            }
        };

        $result = $this->service($handler)->execute(new OperationRequest('acceptance.run', $this->parameters()));

        $this->assertSame('failed', $result->status);
        $this->assertSame('acceptance_hierarchy_invalid', $result->errorCode);
        $this->assertTrue($result->adminActionRequired);
        $this->assertTrue($result->permanent);
    }

    public function test_validation_matrix_precedes_factory_and_handler_side_effects(): void
    {
        $calls = 0;
        $registry = new AcceptanceOperationRegistry;
        foreach (['acceptance.list', 'acceptance.run'] as $name) {
            $registry->register($name, function () use (&$calls): AcceptanceOperationHandler {
                $calls++;
                throw new RuntimeException('example-sensitive-value');
            });
        }
        $service = new AcceptanceOperationService($registry);
        $cases = [
            [new OperationRequest('missing.operation'), 'operation_not_found', []],
            [new OperationRequest('acceptance.list', ['unknown' => 'value']), 'operation_request_invalid', ['parameters']],
            [new OperationRequest('acceptance.list', ['app' => ['key' => 'value']]), 'operation_request_invalid', ['app']],
            [new OperationRequest('acceptance.list', ['limit' => 1.5]), 'operation_request_invalid', ['limit']],
            [new OperationRequest('acceptance.run'), 'operation_request_invalid', ['app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key', 'profile_id']],
            [new OperationRequest('acceptance.run', [...$this->parameters(), 'profile_id' => [], 'headed' => 'yes']), 'operation_request_invalid', ['profile_id', 'headed']],
            [new OperationRequest('acceptance.run', $this->parameters(), correlationId: 'example-sensitive-value'), 'operation_request_invalid', ['correlationId']],
        ];

        foreach ($cases as [$request, $code, $fields]) {
            $result = $service->execute($request);
            $this->assertSame('rejected', $result->status);
            $this->assertSame($code, $result->errorCode);
            $this->assertEqualsCanonicalizing($fields, array_keys($result->errors));
            $this->assertStringNotContainsString('example-sensitive', print_r($result, true));
        }
        $this->assertSame(0, $calls);
    }

    public function test_uuid_reuse_generation_and_invalid_values_are_replaced_safely(): void
    {
        $service = $this->serviceFor('acceptance.list', fn ($request, $correlation): OperationResult => new OperationResult(
            'acceptance.list', 'succeeded', null, $correlation, data: $this->catalog(),
        ));

        $valid = $service->execute(new OperationRequest('acceptance.list', correlationId: self::UUID));
        $this->assertSame(self::UUID, $valid->correlationId);
        $generated = $service->execute(new OperationRequest('acceptance.list'));
        $this->assertTrue(OperationResult::isUuid($generated->correlationId));
        $this->assertNull($generated->operationId);

        foreach (['bad', strtoupper(self::UUID), self::UUID."\n"] as $invalid) {
            $result = $service->execute(new OperationRequest('acceptance.list', correlationId: $invalid));
            $this->assertSame('operation_request_invalid', $result->errorCode);
            $this->assertTrue(OperationResult::isUuid($result->correlationId));
            $this->assertNotSame($invalid, $result->correlationId);
        }
    }

    public function test_run_result_identity_must_match_the_requested_complete_tuple(): void
    {
        $handler = new class implements AcceptanceOperationHandler
        {
            public function name(): string
            {
                return 'acceptance.run';
            }

            public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
            {
                return new OperationResult($this->name(), 'succeeded', null, $correlationId, $operationId, data: new RunOperationData(
                    TestStatusEnum::FINISHED, 10, 'app-a', 'component-other', 'suite-a', 'scenario-a', 'variant-a', null,
                ));
            }
        };

        $result = $this->service($handler)->execute(new OperationRequest('acceptance.run', $this->parameters()));

        $this->assertSame('failed', $result->status);
        $this->assertSame('acceptance_command_failed', $result->errorCode);
        $this->assertNull($result->data);
        $this->assertTrue($result->adminActionRequired);
    }

    public function test_failure_classifications_are_preserved_for_the_version_two_error_set(): void
    {
        $groups = [
            [['acceptance_app_not_found', 'acceptance_profile_not_found', 'acceptance_selector_invalid',
                'acceptance_selector_not_found', 'acceptance_catalog_limit_exceeded', 'acceptance_variant_not_executable',
                'target_module_name_invalid', 'acceptance_hierarchy_key_invalid', 'target_module_not_found',
                'target_module_exists', 'target_module_path_collision', 'acceptance_source_mapping_invalid',
                'acceptance_source_mapping_duplicate', 'prerequisite_request_not_found', 'input_required',
                'approval_required', 'input_invalid', 'secret_literal_forbidden', 'approval_stale',
                'request_expired', 'invalid_transition'], 'rejected', [false, true, false]],
            [['operation_registry_invalid', 'acceptance_registry_invalid', 'acceptance_registry_duplicate',
                'acceptance_catalog_invalid', 'acceptance_hierarchy_invalid', 'acceptance_hierarchy_duplicate',
                'target_module_path_invalid', 'prerequisite_schema_invalid', 'schema_changed'], 'failed', [false, true, true]],
            [['acceptance_configuration_invalid', 'acceptance_scenario_failed', 'acceptance_step_failed'], 'failed', [false, true, false]],
            [['acceptance_catalog_changed', 'conflict'], 'failed', [true, false, false]],
            [['acceptance_browser_start_failed', 'acceptance_result_persistence_failed'], 'failed', [true, false, true]],
            [['acceptance_catalog_failed', 'acceptance_command_failed', 'target_module_generation_failed',
                'target_module_validation_failed', 'prerequisite_persistence_failed', null], 'failed', [null, null, true]],
        ];

        foreach ($groups as [$codes, $status, $classification]) {
            foreach ($codes as $code) {
                Log::swap(Mockery::spy(LogManager::class));
                $service = $this->serviceFor('acceptance.run', fn ($request, $correlation, $operation): OperationResult => new OperationResult(
                    'acceptance.run', $status, $code, $correlation, $operation,
                ));
                $result = $service->execute(new OperationRequest('acceptance.run', $this->parameters()));
                $this->assertSame($status, $result->status);
                $this->assertSame($code, $result->errorCode);
                $this->assertSame($classification, [$result->retryable, $result->permanent, $result->adminActionRequired]);
                Log::shouldHaveReceived('log')->once();
            }
        }
    }

    public function test_exceptions_and_handler_factory_failures_are_normalized_without_raw_values(): void
    {
        foreach (['acceptance.list', 'acceptance.run'] as $operation) {
            foreach ([
                new RuntimeException('example-sensitive-value'),
                new AcceptanceRegistryException('example-sensitive-code', 'example-sensitive-value'),
                AcceptanceRegistryException::duplicate(),
                AcceptanceCatalogException::because('acceptance_selector_invalid'),
            ] as $exception) {
                Log::swap(Mockery::spy(LogManager::class));
                $service = $this->serviceFor($operation, fn () => throw $exception);
                $request = $operation === 'acceptance.run'
                    ? new OperationRequest($operation, $this->parameters())
                    : new OperationRequest($operation);
                $result = $service->execute($request);
                $expected = match (true) {
                    $exception instanceof AcceptanceCatalogException => 'acceptance_selector_invalid',
                    $exception instanceof AcceptanceRegistryException && $exception->errorCode === 'acceptance_registry_duplicate' => 'acceptance_registry_duplicate',
                    default => $operation === 'acceptance.run' ? 'acceptance_command_failed' : 'acceptance_catalog_failed',
                };
                $this->assertSame($expected, $result->errorCode);
                $this->assertStringNotContainsString('example-sensitive', print_r($result, true));
            }
        }

        $registry = new AcceptanceOperationRegistry;
        $registry->register('acceptance.list', fn () => throw new RuntimeException('example-sensitive-value'));
        $result = (new AcceptanceOperationService($registry))->execute(new OperationRequest('acceptance.list'));
        $this->assertSame('operation_registry_invalid', $result->errorCode);
        $this->assertStringNotContainsString('example-sensitive', print_r($result, true));
    }

    public function test_operation_dtos_are_immutable_copied_and_transport_neutral(): void
    {
        $app = 'app-a';
        $request = new OperationRequest('acceptance.list', ['app' => [&$app]]);
        $app = 'changed';
        $this->assertSame(['app' => ['app-a']], $request->parameters);

        $fieldCode = 'operation_request_invalid';
        $result = new OperationResult('acceptance.list', 'rejected', 'operation_request_invalid', self::UUID,
            errors: ['parameters' => [&$fieldCode]]);
        $fieldCode = 'changed';
        $this->assertSame(['parameters' => ['operation_request_invalid']], $result->errors);

        foreach ([OperationRequest::class, OperationResult::class, CatalogOperationData::class, RunOperationData::class,
            FileChange::class, ModuleChangeData::class, ValidationIssue::class, TargetModuleValidationData::class,
            InputRequirement::class, ApprovalRequirement::class, PrerequisiteSchema::class, OperatorContext::class,
            ApprovalFact::class, PrerequisiteOperationData::class] as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue($reflection->isFinal());
            $this->assertTrue($reflection->isReadOnly());
            $this->assertFalse($reflection->implementsInterface(\JsonSerializable::class));
            $this->assertFalse($reflection->hasMethod('toArray'));
        }
    }

    public function test_catalog_snapshots_keep_counts_and_fingerprint_while_plan_filters_items(): void
    {
        $items = [];
        foreach ([AutomationDisposition::AUTOMATED, AutomationDisposition::BLOCKED] as $index => $disposition) {
            $items[] = new AcceptancePlanItem(
                'app-a', 'component-a', 'suite-a', 'scenario-'.$index, 'variant-a',
                new ScenarioMetadata(['capability-a'], ['tag-a'], $disposition, EvidenceMode::METADATA_ONLY),
            );
        }
        $plan = new AcceptancePlan(new AcceptanceSelector, $items, ['app-a' => 'v2']);
        $listed = CatalogOperationData::fromPlan($plan, false);
        $planned = CatalogOperationData::fromPlan($plan, true);

        $this->assertSame([2, 1, 1], [$planned->matched, $planned->executable, $planned->excluded]);
        $this->assertCount(2, $listed->items);
        $this->assertCount(1, $planned->items);
        $this->assertSame($plan->fingerprint, $listed->fingerprint);
        $this->assertSame($listed->fingerprint, $planned->fingerprint);
    }

    public function test_wrong_result_payloads_fail_safely_and_successful_inspection_is_silent(): void
    {
        $callbacks = [
            fn ($request, $correlation): OperationResult => new OperationResult('acceptance.list', 'succeeded', null, $correlation),
            fn ($request, $correlation): OperationResult => new OperationResult('acceptance.list', 'succeeded', null, $correlation,
                data: new RunOperationData(TestStatusEnum::FINISHED, 7, 'app-a', 'component-a', 'suite-a', 'scenario-a', 'variant-a', null)),
        ];
        foreach ($callbacks as $callback) {
            $result = $this->serviceFor('acceptance.list', $callback)->execute(new OperationRequest('acceptance.list'));
            $this->assertSame('acceptance_catalog_failed', $result->errorCode);
            $this->assertNull($result->data);
        }

        Log::swap(Mockery::spy(LogManager::class));
        $result = $this->serviceFor('acceptance.list', fn ($request, $correlation): OperationResult => new OperationResult(
            'acceptance.list', 'succeeded', null, $correlation, data: $this->catalog(),
        ))->execute(new OperationRequest('acceptance.list'));
        $this->assertInstanceOf(CatalogOperationData::class, $result->data);
        Log::shouldNotHaveReceived('log');
        Log::shouldNotHaveReceived('info');
    }

    public function test_prerequisite_operation_rejects_the_wrong_typed_payload(): void
    {
        $operation = 'acceptance.prerequisite.request.cancel';
        $service = $this->serviceFor($operation, fn ($request, $correlation, $operationId): OperationResult => new OperationResult(
            $operation,
            'succeeded',
            null,
            $correlation,
            $operationId,
            data: $this->catalog(),
        ));

        $result = $service->execute(new OperationRequest($operation, [
            'request_id' => self::UUID,
            'expected_lock_version' => 0,
        ]));

        $this->assertSame('failed', $result->status);
        $this->assertSame('prerequisite_persistence_failed', $result->errorCode);
        $this->assertNull($result->data);
    }

    private function service(AcceptanceOperationHandler $handler): AcceptanceOperationService
    {
        $registry = new AcceptanceOperationRegistry;
        $registry->register('acceptance.run', fn (): AcceptanceOperationHandler => $handler);

        return new AcceptanceOperationService($registry);
    }

    private function serviceFor(string $operation, Closure $callback): AcceptanceOperationService
    {
        $registry = new AcceptanceOperationRegistry;
        $registry->register($operation, fn (): AcceptanceOperationHandler => new class($operation, $callback) implements AcceptanceOperationHandler
        {
            public function __construct(private readonly string $operation, private readonly Closure $callback) {}

            public function name(): string
            {
                return $this->operation;
            }

            public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
            {
                return ($this->callback)($request, $correlationId, $operationId);
            }
        });

        return new AcceptanceOperationService($registry);
    }

    private function catalog(): CatalogOperationData
    {
        return CatalogOperationData::fromPlan(new AcceptancePlan(new AcceptanceSelector, [], []), false);
    }

    private function successfulHandler(): AcceptanceOperationHandler
    {
        return new class implements AcceptanceOperationHandler
        {
            public function name(): string
            {
                return 'acceptance.run';
            }

            public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
            {
                $data = new RunOperationData(
                    TestStatusEnum::FINISHED,
                    10,
                    'app-a',
                    'component-a',
                    'suite-a',
                    'scenario-a',
                    'variant-a',
                    null,
                );

                return new OperationResult($this->name(), 'succeeded', null, $correlationId, $operationId, data: $data);
            }
        };
    }

    private function parameters(): array
    {
        return [
            'app_key' => 'app-a',
            'component_key' => 'component-a',
            'suite_key' => 'suite-a',
            'scenario_key' => 'scenario-a',
            'variant_key' => 'variant-a',
            'profile_id' => 1,
        ];
    }
}
