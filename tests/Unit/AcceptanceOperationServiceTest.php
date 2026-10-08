<?php

namespace Tests\Unit;

use App\Acceptance\Operations\AcceptanceOperationRegistry;
use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\CatalogOperationData;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Operations\Data\RunOperationData;
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

    public function test_validation_order_and_fixed_field_errors_precede_all_factory_and_handler_side_effects(): void
    {
        $registry = new AcceptanceOperationRegistry;
        $calls = 0;
        foreach (['acceptance.list', 'acceptance.run'] as $name) {
            $registry->register($name, function () use (&$calls) {
                $calls++;
                throw new RuntimeException('example-sensitive-value');
            });
        }
        $service = new AcceptanceOperationService($registry);
        $cases = [
            [new OperationRequest('example-sensitive-value', ['example-sensitive-field' => 'example-sensitive-value'], 2), 'operation_request_invalid', ['version']],
            [new OperationRequest('example-sensitive-value', ['example-sensitive-field' => new \stdClass]), 'operation_not_found', []],
            [new OperationRequest('acceptance.list', ['example-sensitive-field' => 'example-sensitive-value']), 'operation_request_invalid', ['parameters']],
            [new OperationRequest('acceptance.list', ['app' => ['safe', new \stdClass]]), 'operation_request_invalid', ['app']],
            [new OperationRequest('acceptance.list', ['app' => ['key' => 'safe']]), 'operation_request_invalid', ['app']],
            [new OperationRequest('acceptance.list', ['limit' => 1.5]), 'operation_request_invalid', ['limit']],
            [new OperationRequest('acceptance.run'), 'operation_request_invalid', ['app_key', 'scenario_key', 'profile_id']],
            [new OperationRequest('acceptance.run', ['app_key' => 'safe', 'scenario_key' => 'safe', 'profile_id' => [], 'headed' => 'yes']), 'operation_request_invalid', ['profile_id', 'headed']],
            [new OperationRequest('acceptance.run', [], 2, 'example-sensitive-value'), 'operation_request_invalid', ['version', 'correlationId']],
        ];
        foreach ($cases as [$request, $code, $fields]) {
            $result = $service->execute($request);
            $this->assertSame('rejected', $result->status);
            $this->assertSame($code, $result->errorCode);
            $this->assertEqualsCanonicalizing($fields, array_keys($result->errors));
            foreach ($result->errors as $errors) {
                $this->assertSame(['operation_request_invalid'], $errors);
            }
            $this->assertSame($request->operation === 'acceptance.run', $result->operationId !== null);
            $this->assertSame(str_starts_with($request->operation, 'example') ? null : $request->operation, $result->operation);
            $this->assertSafeLog($result, 'warning');
            $this->assertStringNotContainsString('example-sensitive', print_r($result, true));
        }
        $this->assertSame(0, $calls);
    }

    public function test_uuid_reuse_generation_and_invalid_upstream_replacement_are_safe(): void
    {
        $service = $this->service('acceptance.list', fn ($request, $correlation, $operation) => new OperationResult('acceptance.list', 'succeeded', null, $correlation, data: $this->catalog()));
        $valid = $service->execute(new OperationRequest('acceptance.list', correlationId: self::UUID));
        $this->assertSame(self::UUID, $valid->correlationId);
        $generated = $service->execute(new OperationRequest('acceptance.list'));
        $this->assertTrue(OperationResult::isUuid($generated->correlationId));
        $this->assertSame('4', $generated->correlationId[14]);
        $this->assertNull($generated->operationId);
        foreach (['example-sensitive-value', strtoupper(self::UUID), 'dcb1cf9d-207c-0a44-963b-000000000009',
            'dcb1cf9d-207c-4a44-763b-000000000009', self::UUID."\n"] as $uuid) {
            $result = $service->execute(new OperationRequest('acceptance.list', correlationId: $uuid));
            $this->assertSame('operation_request_invalid', $result->errorCode);
            $this->assertTrue(OperationResult::isUuid($result->correlationId));
            $this->assertNotSame($uuid, $result->correlationId);
            $this->assertSafeLog($result, 'warning');
        }
    }

    public function test_dispatch_passes_the_same_request_and_traces_and_rebuilds_handler_owned_identity_and_flags(): void
    {
        $request = new OperationRequest('acceptance.run', ['app_key' => 'safe', 'scenario_key' => 'safe', 'profile_id' => 1], correlationId: self::UUID);
        $seen = [];
        $service = $this->service('acceptance.run', function ($actual, $correlation, $operation) use (&$seen, $request) {
            $this->assertSame($request, $actual);
            $seen = [$correlation, $operation];

            return new OperationResult('acceptance.plan', 'failed', 'acceptance_step_failed', self::UUID,
                self::UUID, true, false, true,
                data: new RunOperationData(TestStatusEnum::FAILED, 7, 'safe', 'safe', 'acceptance_step_failed'));
        });
        $result = $service->execute($request);
        $this->assertSame('acceptance.run', $result->operation);
        $this->assertSame($seen, [$result->correlationId, $result->operationId]);
        $this->assertNotSame(self::UUID, $result->operationId);
        $this->assertSame([false, true, false], [$result->retryable, $result->permanent, $result->adminActionRequired]);
        $this->assertSafeLog($result, 'error');
    }

    public function test_every_failure_classification_is_observable_in_the_result_and_exact_boundary_log(): void
    {
        $groups = [
            [['acceptance_app_not_found', 'acceptance_scenario_not_found', 'acceptance_profile_not_found', 'acceptance_selector_invalid',
                'acceptance_selector_not_found', 'acceptance_catalog_limit_exceeded', 'acceptance_variant_not_executable'], 'rejected', [false, true, false]],
            [['operation_registry_invalid', 'acceptance_registry_invalid', 'acceptance_registry_duplicate', 'acceptance_catalog_invalid', 'acceptance_catalog_duplicate'], 'failed', [false, true, true]],
            [['acceptance_configuration_invalid', 'acceptance_scenario_failed', 'acceptance_step_failed'], 'failed', [false, true, false]],
            [['acceptance_catalog_changed'], 'failed', [true, false, false]],
            [['acceptance_browser_start_failed', 'acceptance_result_persistence_failed'], 'failed', [true, false, true]],
            [['acceptance_catalog_failed', 'acceptance_command_failed', null], 'failed', [null, null, true]],
        ];
        foreach ($groups as [$codes, $status, $classification]) {
            foreach ($codes as $code) {
                Log::swap(Mockery::spy(LogManager::class));
                $service = $this->service('acceptance.run', fn ($request, $correlation, $operation) => new OperationResult('acceptance.run', $status, $code, $correlation, $operation));
                $result = $service->execute($this->runRequest());
                $this->assertSame($status, $result->status);
                $this->assertSame($code, $result->errorCode);
                $this->assertSame($classification, [$result->retryable, $result->permanent, $result->adminActionRequired]);
                $this->assertSafeLog($result, $status === 'rejected' ? 'warning' : 'error');
                Log::shouldHaveReceived('log')->once();
            }
        }
    }

    public function test_known_and_arbitrary_exception_codes_and_factory_failures_are_normalized_without_raw_values(): void
    {
        foreach (['acceptance.list', 'acceptance.run'] as $operation) {
            foreach ([new RuntimeException('example-sensitive-value'),
                new AcceptanceRegistryException('example-sensitive-code', 'example-sensitive-value'),
                AcceptanceRegistryException::duplicate(), AcceptanceCatalogException::because('acceptance_selector_invalid')] as $exception) {
                Log::swap(Mockery::spy(LogManager::class));
                $service = $this->service($operation, fn () => throw $exception);
                $result = $service->execute($operation === 'acceptance.run' ? $this->runRequest() : new OperationRequest($operation));
                $expected = match (true) {
                    $exception instanceof AcceptanceCatalogException => 'acceptance_selector_invalid',
                    $exception instanceof AcceptanceRegistryException && $exception->errorCode === 'acceptance_registry_duplicate' => 'acceptance_registry_duplicate',
                    default => $operation === 'acceptance.run' ? 'acceptance_command_failed' : 'acceptance_catalog_failed',
                };
                $this->assertSame($expected, $result->errorCode);
                $this->assertSafeLog($result, $result->status === 'rejected' ? 'warning' : 'error');
                $this->assertStringNotContainsString('example-sensitive', print_r($result, true));
            }
        }
        $registry = new AcceptanceOperationRegistry;
        $registry->register('acceptance.list', fn () => throw new RuntimeException('example-sensitive-value'));
        $result = (new AcceptanceOperationService($registry))->execute(new OperationRequest('acceptance.list'));
        $this->assertSame('operation_registry_invalid', $result->errorCode);
        $this->assertSafeLog($result, 'error');
    }

    public function test_payloads_and_results_are_immutable_copied_safe_data_without_client_serializers(): void
    {
        $app = 'safe';
        $tag = 'tag';
        $request = new OperationRequest('acceptance.list', ['app' => [&$app], 'tag' => [&$tag]]);
        $app = 'changed';
        $tag = 'changed';
        $this->assertSame(['app' => ['safe'], 'tag' => ['tag']], $request->parameters);
        $version = 'v1';
        $count = 0;
        $fieldCode = 'operation_request_invalid';
        $data = new CatalogOperationData(1, ['safe' => &$version], 0, 0, 0,
            ['automated' => &$count, 'manual-only' => 0, 'blocked' => 0, 'not-implemented' => 0], str_repeat('a', 64), []);
        $result = new OperationResult('acceptance.list', 'rejected', 'operation_request_invalid', self::UUID,
            errors: ['parameters' => [&$fieldCode]]);
        $version = 'changed';
        $count = 9;
        $fieldCode = 'example-sensitive-value';
        $this->assertSame(['safe' => 'v1'], $data->catalogVersions);
        $this->assertSame(0, $data->byDisposition['automated']);
        $this->assertSame(['parameters' => ['operation_request_invalid']], $result->errors);
        foreach ([OperationRequest::class, OperationResult::class, CatalogOperationData::class, RunOperationData::class] as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue($reflection->isFinal());
            $this->assertTrue($reflection->isReadOnly());
            $this->assertFalse($reflection->implementsInterface(\JsonSerializable::class));
            $this->assertFalse($reflection->hasMethod('toArray'));
        }
        $this->expectException(\Error::class);
        $request->parameters['app'] = ['changed'];
    }

    public function test_catalog_snapshots_keep_all_counts_and_fingerprint_while_plan_filters_only_items(): void
    {
        $items = [];
        foreach ([AutomationDisposition::AUTOMATED, AutomationDisposition::MANUAL_ONLY] as $disposition) {
            $items[] = new AcceptancePlanItem('safe', $disposition->value, 'default',
                new ScenarioMetadata(['suite'], ['capability'], ['tag'], $disposition, EvidenceMode::METADATA_ONLY));
        }
        $plan = new AcceptancePlan(new AcceptanceSelector, $items, ['safe' => 'v1']);
        $list = CatalogOperationData::fromPlan($plan, false);
        $planned = CatalogOperationData::fromPlan($plan, true);
        $this->assertSame([2, 1, 1], [$planned->matched, $planned->executable, $planned->excluded]);
        $this->assertCount(2, $list->items);
        $this->assertCount(1, $planned->items);
        $this->assertSame($list->fingerprint, $planned->fingerprint);
        $this->assertSame($plan->fingerprint, $planned->fingerprint);
    }

    public function test_wrong_payloads_or_raw_result_fields_fail_safely_and_successful_inspection_is_silent(): void
    {
        foreach ([fn ($r, $c, $o) => new OperationResult('acceptance.list', 'succeeded', null, $c),
            fn ($r, $c, $o) => new OperationResult('acceptance.list', 'succeeded', null, $c,
                data: new RunOperationData(TestStatusEnum::FINISHED, 7, 'safe', 'safe', null)),
            fn ($r, $c, $o) => new OperationResult('acceptance.list', 'failed', 'example-sensitive-code', $c),
            fn ($r, $c, $o) => new OperationResult('acceptance.list', 'rejected', 'operation_request_invalid', $c,
                errors: ['example-sensitive-field' => ['operation_request_invalid']])] as $callback) {
            $result = $this->service('acceptance.list', $callback)->execute(new OperationRequest('acceptance.list'));
            $this->assertSame('acceptance_catalog_failed', $result->errorCode);
            $this->assertSafeLog($result, 'error');
        }
        Log::swap(Mockery::spy(LogManager::class));
        $result = $this->service('acceptance.list', fn ($r, $c, $o) => new OperationResult('acceptance.list', 'succeeded', null, $c, data: $this->catalog()))
            ->execute(new OperationRequest('acceptance.list'));
        $this->assertInstanceOf(CatalogOperationData::class, $result->data);
        Log::shouldNotHaveReceived('log');
        Log::shouldNotHaveReceived('info');
    }

    private function catalog(): CatalogOperationData
    {
        return CatalogOperationData::fromPlan(new AcceptancePlan(new AcceptanceSelector, [], []), false);
    }

    private function runRequest(): OperationRequest
    {
        return new OperationRequest('acceptance.run', ['app_key' => 'safe', 'scenario_key' => 'safe', 'profile_id' => 1]);
    }

    private function service(string $operation, Closure $callback): AcceptanceOperationService
    {
        $registry = new AcceptanceOperationRegistry;
        $registry->register($operation, fn () => new class($operation, $callback) implements AcceptanceOperationHandler
        {
            public function __construct(private string $operation, private Closure $callback) {}

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

    private function assertSafeLog(OperationResult $result, string $level): void
    {
        Log::shouldHaveReceived('log')->with($level, 'tms.acceptance.operation.failed',
            Mockery::on(function (array $context) use ($result): bool {
                if (($context['correlation_id'] ?? null) !== $result->correlationId) {
                    return false;
                }
                $expected = [
                    'operation' => $result->operation, 'error_code' => $result->errorCode,
                    'correlation_id' => $result->correlationId, 'operation_id' => $result->operationId,
                    'retryable' => $result->retryable, 'permanent' => $result->permanent,
                    'admin_action_required' => $result->adminActionRequired,
                ];
                if ($result->data instanceof RunOperationData) {
                    $expected['test_id'] = $result->data->testId;
                }
                $withoutClass = $context;
                unset($withoutClass['exception_class']);
                $this->assertSame($expected, $withoutClass);
                $this->assertStringNotContainsString('example-sensitive', print_r($context, true));

                return true;
            }));
    }
}
