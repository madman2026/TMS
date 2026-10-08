<?php

namespace Tests\Feature;

use App\Acceptance\Operations\AcceptanceOperationRegistry;
use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Prerequisites\Contracts\OperatorContextProvider;
use App\Acceptance\Prerequisites\Data\ApprovalRequirement;
use App\Acceptance\Prerequisites\Data\InputRequirement;
use App\Acceptance\Prerequisites\Data\OperatorContext;
use App\Acceptance\Prerequisites\Data\PrerequisiteOperationData;
use App\Acceptance\Prerequisites\Data\PrerequisiteSchema;
use App\Acceptance\Prerequisites\Enums\InputSensitivity;
use App\Acceptance\Prerequisites\Enums\InputType;
use App\Acceptance\Prerequisites\Enums\PrerequisiteState;
use App\Contracts\AcceptanceComponentProvider;
use App\Data\ComponentDescriptor;
use App\Data\ScenarioDescriptor;
use App\Data\SuiteDescriptor;
use App\Data\VariantDescriptor;
use App\Models\AcceptanceOperationApproval;
use App\Models\AcceptanceOperationInput;
use App\Models\AcceptanceOperationRequest;
use App\Models\Profile;
use App\Models\User;
use App\Services\AcceptanceAppRegistry;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Tests\TestCase;

class AcceptancePrerequisiteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_four_operations_persist_and_restore_a_complete_prerequisite_workflow(): void
    {
        $this->travelTo('2026-10-08 10:00:00');
        $this->registerProvider($this->schema());
        $profile = Profile::factory()->for(User::factory())->create();
        $service = $this->app->make(AcceptanceOperationService::class);

        $prepared = $service->execute(new OperationRequest('acceptance.prerequisite.request.prepare', [
            ...$this->identity(),
            'profile_id' => $profile->getKey(),
        ]));
        $this->assertSame('succeeded', $prepared->status);
        $this->assertInstanceOf(PrerequisiteOperationData::class, $prepared->data);
        $this->assertSame($prepared->operationId, $prepared->data->requestId);
        $this->assertSame(PrerequisiteState::AWAITING_INPUT, $prepared->data->state);

        $this->app->forgetInstance(AcceptanceOperationService::class);
        $restored = $this->app->make(AcceptanceOperationService::class)->execute(new OperationRequest(
            'acceptance.prerequisite.request.prepare',
            ['request_id' => $prepared->data->requestId],
        ));
        $this->assertSame($prepared->data->requestId, $restored->data->requestId);
        $this->assertSame($prepared->data->schemaFingerprint, $restored->data->schemaFingerprint);

        $submitted = $service->execute(new OperationRequest('acceptance.prerequisite.input.submit', [
            'request_id' => $prepared->data->requestId,
            'expected_lock_version' => 0,
            'inputs' => [
                'username' => ['source' => 'literal', 'value' => 'operator-a'],
                'credential' => ['source' => 'secret_reference', 'value' => 'app-secret://app-a/reference-a'],
            ],
        ]));
        $this->assertSame(PrerequisiteState::AWAITING_APPROVAL, $submitted->data->state);
        $this->assertSame(1, $submitted->data->lockVersion);
        $this->assertStringNotContainsString('app-secret://app-a/reference-a', print_r($submitted->data, true));
        $username = AcceptanceOperationInput::query()->where('key', 'username')->firstOrFail();
        $credential = AcceptanceOperationInput::query()->where('key', 'credential')->firstOrFail();
        $this->assertSame('operator-a', $username->value_json);
        $this->assertNull($username->secret_reference);
        $this->assertNull($credential->value_json);
        $this->assertSame('app-secret://app-a/reference-a', $credential->secret_reference);

        $approved = $service->execute(new OperationRequest('acceptance.prerequisite.approval.grant', [
            'request_id' => $prepared->data->requestId,
            'expected_lock_version' => 1,
            'scope' => 'deploy',
        ]));
        $this->assertSame(PrerequisiteState::READY, $approved->data->state);
        $this->assertSame('test_actor', $approved->data->approvalFacts[0]->actorType);
        $this->assertSame('test-actor', $approved->data->approvalFacts[0]->actorReference);

        $cancelled = $service->execute(new OperationRequest('acceptance.prerequisite.request.cancel', [
            'request_id' => $prepared->data->requestId,
            'expected_lock_version' => 2,
        ]));
        $this->assertSame(PrerequisiteState::CANCELLED, $cancelled->data->state);
        $this->assertNotNull(AcceptanceOperationRequest::query()->findOrFail($prepared->data->requestId)->cancelled_at);
    }

    public function test_migrations_models_relationships_indexes_and_restrictive_foreign_keys_match_the_contract(): void
    {
        $this->registerProvider($this->schema());
        $profile = Profile::factory()->for(User::factory())->create();
        $result = $this->app->make(AcceptanceOperationService::class)->execute(new OperationRequest(
            'acceptance.prerequisite.request.prepare',
            [...$this->identity(), 'profile_id' => $profile->getKey()],
        ));

        $this->assertTrue(Schema::hasColumns('acceptance_operation_requests', [
            'id', 'correlation_id', 'app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key',
            'profile_id', 'schema_version', 'schema_fingerprint', 'state', 'lock_version', 'expires_at', 'cancelled_at',
        ]));
        $this->assertTrue(Schema::hasColumns('acceptance_operation_inputs', [
            'acceptance_operation_request_id', 'key', 'schema_version', 'type', 'sensitivity', 'value_json',
            'secret_reference', 'value_fingerprint', 'submitted_by_type', 'submitted_by_reference', 'submitted_at',
        ]));
        $this->assertTrue(Schema::hasColumns('acceptance_operation_approvals', [
            'acceptance_operation_request_id', 'scope', 'schema_version', 'input_fingerprint', 'actor_type',
            'actor_reference', 'approved_at', 'expires_at', 'revoked_at',
        ]));

        $requestIndexes = collect(DB::select("PRAGMA index_list('acceptance_operation_requests')"))->pluck('name')->all();
        $inputIndexes = collect(DB::select("PRAGMA index_list('acceptance_operation_inputs')"))->pluck('name')->all();
        $approvalIndexes = collect(DB::select("PRAGMA index_list('acceptance_operation_approvals')"))->pluck('name')->all();
        $this->assertContains('acceptance_request_hierarchy_index', $requestIndexes);
        $this->assertContains('acceptance_request_profile_index', $requestIndexes);
        $this->assertContains('acceptance_request_state_expiry_index', $requestIndexes);
        $this->assertContains('acceptance_input_request_key_schema_unique', $inputIndexes);
        $this->assertContains('acceptance_approval_request_scope_schema_fingerprint_unique', $approvalIndexes);

        $inputForeign = DB::select("PRAGMA foreign_key_list('acceptance_operation_inputs')")[0];
        $approvalForeign = DB::select("PRAGMA foreign_key_list('acceptance_operation_approvals')")[0];
        $requestForeign = DB::select("PRAGMA foreign_key_list('acceptance_operation_requests')")[0];
        $this->assertSame('profiles', $requestForeign->table);
        $this->assertSame('RESTRICT', $requestForeign->on_delete);
        $this->assertSame('CASCADE', $requestForeign->on_update);
        $this->assertSame('acceptance_operation_requests', $inputForeign->table);
        $this->assertSame('RESTRICT', $inputForeign->on_delete);
        $this->assertSame('CASCADE', $inputForeign->on_update);
        $this->assertSame('acceptance_operation_requests', $approvalForeign->table);
        $this->assertSame('RESTRICT', $approvalForeign->on_delete);

        $request = AcceptanceOperationRequest::query()->findOrFail($result->data->requestId);
        $this->assertSame(PrerequisiteState::AWAITING_INPUT, $request->state);
        $this->assertTrue($request->profile->is($profile));
        $this->assertCount(0, $request->inputs);
        $this->assertCount(0, $request->approvals);

        try {
            $profile->delete();
            $this->fail('Expected restrictive profile foreign key.');
        } catch (QueryException) {
            $this->assertDatabaseHas('profiles', ['id' => $profile->getKey()]);
        }
    }

    public function test_literal_secret_rejection_returns_safe_classification_and_never_leaks_the_sentinel(): void
    {
        $this->registerProvider($this->schema());
        $profile = Profile::factory()->for(User::factory())->create();
        $service = $this->app->make(AcceptanceOperationService::class);
        $prepared = $service->execute(new OperationRequest('acceptance.prerequisite.request.prepare', [
            ...$this->identity(),
            'profile_id' => $profile->getKey(),
        ]));
        Log::swap(Mockery::spy(LogManager::class));

        $result = $service->execute(new OperationRequest('acceptance.prerequisite.input.submit', [
            'request_id' => $prepared->data->requestId,
            'expected_lock_version' => 0,
            'inputs' => [
                'credential' => ['source' => 'literal', 'value' => 'inert-secret-sentinel'],
            ],
        ]));

        $this->assertSame('rejected', $result->status);
        $this->assertSame('secret_literal_forbidden', $result->errorCode);
        $this->assertFalse($result->retryable);
        $this->assertTrue($result->permanent);
        $this->assertFalse($result->adminActionRequired);
        $this->assertStringNotContainsString('inert-secret-sentinel', print_r($result, true));
        $this->assertDatabaseMissing('acceptance_operation_inputs', ['key' => 'credential']);
        $this->assertSame(0, AcceptanceOperationInput::query()->count());
        $this->assertSame(0, AcceptanceOperationApproval::query()->count());
        Log::shouldHaveReceived('log')->once()->withArgs(
            fn (string $level, string $event, array $context): bool => $level === 'warning'
                && $event === 'tms.acceptance.operation.failed'
                && $context['operation'] === 'acceptance.prerequisite.input.submit'
                && $context['error_code'] === 'secret_literal_forbidden'
                && ! str_contains(print_r($context, true), 'inert-secret-sentinel'),
        );
    }

    public function test_request_shapes_are_rejected_before_lazy_handler_resolution(): void
    {
        $calls = 0;
        $registry = new AcceptanceOperationRegistry;
        foreach ([
            'acceptance.prerequisite.request.prepare',
            'acceptance.prerequisite.input.submit',
            'acceptance.prerequisite.approval.grant',
            'acceptance.prerequisite.request.cancel',
        ] as $operation) {
            $registry->register($operation, function () use (&$calls) {
                $calls++;
                throw new \RuntimeException('must-not-resolve');
            });
        }
        $service = new AcceptanceOperationService($registry);
        $cases = [
            new OperationRequest('acceptance.prerequisite.request.prepare', ['request_id' => 'invalid']),
            new OperationRequest('acceptance.prerequisite.request.prepare', [...$this->identity(), 'profile_id' => 1, 'request_id' => $this->uuid()]),
            new OperationRequest('acceptance.prerequisite.input.submit', ['request_id' => $this->uuid(), 'expected_lock_version' => -1, 'inputs' => []]),
            new OperationRequest('acceptance.prerequisite.approval.grant', ['request_id' => $this->uuid(), 'expected_lock_version' => 0, 'scope' => 'Invalid Scope']),
            new OperationRequest('acceptance.prerequisite.request.cancel', ['request_id' => $this->uuid()]),
        ];

        foreach ($cases as $request) {
            $result = $service->execute($request);
            $this->assertSame('rejected', $result->status);
            $this->assertSame('operation_request_invalid', $result->errorCode);
        }
        $this->assertSame(0, $calls);
    }

    public function test_state_change_logs_use_the_fixed_safe_context_allowlist(): void
    {
        $this->registerProvider($this->schema());
        $profile = Profile::factory()->for(User::factory())->create();
        $service = $this->app->make(AcceptanceOperationService::class);
        $correlationId = $this->uuid();
        Log::swap(Mockery::spy(LogManager::class));
        $prepared = $service->execute(new OperationRequest('acceptance.prerequisite.request.prepare', [
            ...$this->identity(),
            'profile_id' => $profile->getKey(),
        ], correlationId: $correlationId));

        $service->execute(new OperationRequest('acceptance.prerequisite.input.submit', [
            'request_id' => $prepared->data->requestId,
            'expected_lock_version' => 0,
            'inputs' => [
                'username' => ['source' => 'literal', 'value' => 'operator-a'],
                'credential' => ['source' => 'secret_reference', 'value' => 'app-secret://app-a/reference-a'],
            ],
        ], correlationId: $correlationId));

        Log::shouldHaveReceived('info')->withArgs(
            fn (string $event, array $context): bool => $event === 'tms.acceptance.prerequisite.state_changed'
                && $context['operation'] === 'acceptance.prerequisite.input.submit'
                && $context['previous_state'] === 'awaiting_input'
                && $context['current_state'] === 'awaiting_approval'
                && $context['changed_requirement_keys'] === ['username', 'credential']
                && $context['invalidated_approval_scopes'] === []
                && $context['actor_type'] === 'test_actor'
                && $context['actor_reference'] === 'test-actor'
                && $context['correlation_id'] === $correlationId
                && $context['app_key'] === 'app-a'
                && $context['component_key'] === 'component-a'
                && $context['suite_key'] === 'suite-a'
                && $context['scenario_key'] === 'scenario-a'
                && $context['variant_key'] === 'variant-a'
                && array_keys($context) === [
                    'operation_request_id', 'operation', 'previous_state', 'current_state',
                    'changed_requirement_keys', 'invalidated_approval_scopes', 'actor_type',
                    'actor_reference', 'correlation_id', 'app_key', 'component_key', 'suite_key',
                    'scenario_key', 'variant_key',
                ]
                && ! str_contains(print_r($context, true), 'operator-a')
                && ! str_contains(print_r($context, true), 'app-secret://'),
        )->once();
    }

    private function registerProvider(PrerequisiteSchema $schema): void
    {
        $this->app->instance(OperatorContextProvider::class, new class implements OperatorContextProvider
        {
            public function current(): OperatorContext
            {
                return new OperatorContext('test_actor', 'test-actor');
            }
        });
        $this->app->make(AcceptanceAppRegistry::class)->register(new class($schema) implements AcceptanceComponentProvider
        {
            public function __construct(private readonly PrerequisiteSchema $schema) {}

            public function key(): string
            {
                return 'app-a';
            }

            public function catalogVersion(): string
            {
                return 'v2';
            }

            public function components(): iterable
            {
                yield new ComponentDescriptor('component-a');
            }

            public function suites(): iterable
            {
                yield new SuiteDescriptor('suite-a', 'component-a');
            }

            public function scenarios(): iterable
            {
                yield new ScenarioDescriptor(
                    'scenario-a',
                    'component-a',
                    'suite-a',
                    new ScenarioMetadata(
                        ['capability-a'],
                        ['tag-a'],
                        AutomationDisposition::AUTOMATED,
                        EvidenceMode::METADATA_ONLY,
                    ),
                );
            }

            public function variants(string $scenarioKey): iterable
            {
                if ($scenarioKey === 'scenario-a') {
                    yield new VariantDescriptor('variant-a', $this->schema);
                }
            }

            public function resolveScenario(
                string $componentKey,
                string $suiteKey,
                string $scenarioKey,
                string $variantKey,
            ): ?AcceptanceScenario {
                return null;
            }
        });
    }

    private function schema(): PrerequisiteSchema
    {
        return new PrerequisiteSchema('v1', [
            new InputRequirement('username', InputType::STRING, approvalRelevant: true),
            new InputRequirement('credential', InputType::SECRET_REFERENCE, InputSensitivity::SECRET_REFERENCE, approvalRelevant: true),
        ], [
            new ApprovalRequirement('deploy', invalidatedByInputKeys: ['username', 'credential']),
        ]);
    }

    /** @return array{app_key: string, component_key: string, suite_key: string, scenario_key: string, variant_key: string} */
    private function identity(): array
    {
        return [
            'app_key' => 'app-a',
            'component_key' => 'component-a',
            'suite_key' => 'suite-a',
            'scenario_key' => 'scenario-a',
            'variant_key' => 'variant-a',
        ];
    }

    private function uuid(): string
    {
        return '123e4567-e89b-42d3-a456-426614174000';
    }
}
