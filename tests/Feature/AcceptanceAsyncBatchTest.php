<?php

namespace Tests\Feature;

use App\Acceptance\Execution\AcceptanceBatchService;
use App\Acceptance\Execution\AcceptanceExecutionException;
use App\Acceptance\Execution\Data\BatchItemOperationData;
use App\Acceptance\Execution\Data\BatchOperationData;
use App\Acceptance\Execution\Data\BatchPrerequisiteReference;
use App\Acceptance\Execution\Enums\AttemptState;
use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\OperationState;
use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Prerequisites\Data\InputRequirement;
use App\Acceptance\Prerequisites\Data\PrerequisiteSchema;
use App\Acceptance\Prerequisites\Enums\InputType;
use App\Contracts\AcceptanceComponentProvider;
use App\Data\ComponentDescriptor;
use App\Data\ScenarioDescriptor;
use App\Data\SuiteDescriptor;
use App\Data\VariantDescriptor;
use App\Events\AcceptanceExecutionStateChanged;
use App\Jobs\RunAcceptanceBatch;
use App\Models\AcceptanceBatch;
use App\Models\AcceptanceExecutionAttempt;
use App\Models\Profile;
use App\Models\User;
use App\Services\AcceptanceAppRegistry;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Tests\TestCase;

class AcceptanceAsyncBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_async_start_persists_the_plan_and_dispatches_only_identifiers(): void
    {
        Queue::fake();
        Event::fake([AcceptanceExecutionStateChanged::class]);
        $this->registerProvider(PrerequisiteSchema::none());
        $profile = Profile::factory()->for(User::factory())->create();

        $result = app(AcceptanceOperationService::class)->execute(new OperationRequest('acceptance.batch.start', [
            'app' => ['app-a'],
            'component' => ['component-a'],
            'suite' => ['suite-a'],
            'scenario' => ['scenario-a'],
            'variant' => ['variant-a'],
            'profile_id' => $profile->id,
            'mode' => 'async',
        ]));

        $this->assertSame('succeeded', $result->status);
        $this->assertInstanceOf(BatchOperationData::class, $result->data);
        $this->assertSame(BatchState::QUEUED, $result->data->batchState);
        $this->assertSame(OperationState::QUEUED, $result->data->operationState);
        $this->assertSame(1, $result->data->pendingCount);
        $this->assertDatabaseHas('acceptance_batch_items', [
            'batch_id' => $result->data->batchId,
            'app_key' => 'app-a',
            'state' => BatchItemState::PENDING->value,
        ]);
        Queue::assertPushed(RunAcceptanceBatch::class, function (RunAcceptanceBatch $job) use ($result): bool {
            $payload = serialize($job);

            return $job->batchId === $result->data->batchId
                && $job->operationId === $result->operationId
                && ! str_contains($payload, 'sensitive-sentinel')
                && ! str_contains($payload, 'selector_snapshot');
        });
        Event::assertDispatched(AcceptanceExecutionStateChanged::class, fn (AcceptanceExecutionStateChanged $event): bool => $event->batchId === $result->data->batchId
            && $event->entity === 'batch' && $event->currentState === BatchState::QUEUED->value);
    }

    public function test_prerequisite_gate_keeps_batch_planned_without_dispatching(): void
    {
        Queue::fake();
        $this->registerProvider(new PrerequisiteSchema('v1', [
            new InputRequirement('account', InputType::STRING),
        ]));
        $profile = Profile::factory()->for(User::factory())->create();

        $result = app(AcceptanceOperationService::class)->execute(new OperationRequest('acceptance.batch.start', [
            'app' => ['app-a'],
            'profile_id' => $profile->id,
            'mode' => 'async',
        ]));

        $this->assertSame('succeeded', $result->status);
        $this->assertSame(BatchState::PLANNED, $result->data->batchState);
        $this->assertSame(OperationState::AWAITING_INPUT, $result->data->operationState);
        $this->assertSame(1, $result->data->blockedCount);
        Queue::assertNothingPushed();
    }

    public function test_resume_dispatches_after_the_prerequisite_request_becomes_ready(): void
    {
        Queue::fake();
        $this->registerProvider(new PrerequisiteSchema('v1', [
            new InputRequirement('account', InputType::STRING),
        ]));
        $profile = Profile::factory()->for(User::factory())->create();
        $service = app(AcceptanceOperationService::class);
        $started = $service->execute(new OperationRequest('acceptance.batch.start', [
            'app' => ['app-a'],
            'profile_id' => $profile->id,
            'mode' => 'async',
        ]));
        $prepared = $service->execute(new OperationRequest('acceptance.prerequisite.request.prepare', [
            'app_key' => 'app-a',
            'component_key' => 'component-a',
            'suite_key' => 'suite-a',
            'scenario_key' => 'scenario-a',
            'variant_key' => 'variant-a',
            'profile_id' => $profile->id,
        ]));
        $submitted = $service->execute(new OperationRequest('acceptance.prerequisite.input.submit', [
            'request_id' => $prepared->data->requestId,
            'expected_lock_version' => $prepared->data->lockVersion,
            'inputs' => ['account' => ['source' => 'literal', 'value' => 'synthetic-account']],
        ]));

        $resumed = $service->execute(new OperationRequest('acceptance.batch.resume', [
            'batch_id' => $started->data->batchId,
            'operation_id' => $started->operationId,
            'expected_lock_version' => $started->data->lockVersion,
            'prerequisite_references' => [new BatchPrerequisiteReference(
                'app-a', 'component-a', 'suite-a', 'scenario-a', 'variant-a', $submitted->data->requestId,
            )],
        ]));

        $this->assertSame('succeeded', $resumed->status);
        $this->assertSame(BatchState::QUEUED, $resumed->data->batchState);
        $this->assertSame(OperationState::QUEUED, $resumed->data->operationState);
        $this->assertSame(1, $resumed->data->pendingCount);
        Queue::assertPushed(RunAcceptanceBatch::class);
    }

    public function test_cancel_is_optimistic_and_terminal_without_running_items(): void
    {
        Queue::fake();
        $this->registerProvider(PrerequisiteSchema::none());
        $profile = Profile::factory()->for(User::factory())->create();
        $service = app(AcceptanceOperationService::class);
        $started = $service->execute(new OperationRequest('acceptance.batch.start', [
            'app' => ['app-a'],
            'profile_id' => $profile->id,
            'mode' => 'async',
        ]));

        $cancelled = $service->execute(new OperationRequest('acceptance.batch.cancel', [
            'batch_id' => $started->data->batchId,
            'operation_id' => $started->operationId,
            'expected_lock_version' => $started->data->lockVersion,
        ]));

        $this->assertSame('succeeded', $cancelled->status);
        $this->assertSame(BatchState::CANCELLED, $cancelled->data->batchState);
        $this->assertSame(OperationState::CANCELLED, $cancelled->data->operationState);
        $this->assertSame(1, $cancelled->data->cancelledCount);
        $this->assertNotNull(AcceptanceBatch::findOrFail($started->data->batchId)->cancel_requested_at);
    }

    public function test_dispatch_wave_never_exceeds_four_in_flight_items(): void
    {
        Bus::fake();
        $variants = ['variant-1', 'variant-2', 'variant-3', 'variant-4', 'variant-5'];
        $this->registerProvider(PrerequisiteSchema::none(), $variants);
        $profile = Profile::factory()->for(User::factory())->create();
        $started = app(AcceptanceOperationService::class)->execute(new OperationRequest('acceptance.batch.start', [
            'app' => ['app-a'],
            'profile_id' => $profile->id,
            'mode' => 'async',
        ]));

        app(AcceptanceBatchService::class)->dispatchWave(
            $started->data->batchId,
            $started->operationId,
        );
        app(AcceptanceBatchService::class)->dispatchWave(
            $started->data->batchId,
            $started->operationId,
        );

        Bus::assertBatched(fn (PendingBatch $batch): bool => $batch->jobs->count() === 4);
        $this->assertDatabaseCount('acceptance_execution_attempts', 4);
        $this->assertDatabaseCount('acceptance_batch_items', 5);
        $this->assertSame(4, AcceptanceBatch::findOrFail($started->data->batchId)
            ->items()->where('state', BatchItemState::QUEUED)->count());
        $this->assertSame(1, AcceptanceBatch::findOrFail($started->data->batchId)
            ->items()->where('state', BatchItemState::PENDING)->count());
    }

    public function test_safe_retry_creates_a_child_batch_and_preserves_lineage(): void
    {
        Queue::fake();
        $this->registerProvider(PrerequisiteSchema::none());
        $profile = Profile::factory()->for(User::factory())->create();
        $service = app(AcceptanceOperationService::class);
        $started = $service->execute(new OperationRequest('acceptance.batch.start', [
            'app' => ['app-a'],
            'profile_id' => $profile->id,
            'mode' => 'async',
        ]));
        $source = AcceptanceBatch::findOrFail($started->data->batchId)->items()->firstOrFail();
        $source->update([
            'state' => BatchItemState::FAILED,
            'retryable' => true,
            'permanent' => false,
            'finished_at' => now(),
        ]);

        $retried = $service->execute(new OperationRequest('acceptance.batch.item.retry', [
            'item_id' => $source->id,
            'expected_lock_version' => $source->lock_version,
        ]));

        $this->assertSame('succeeded', $retried->status);
        $this->assertInstanceOf(BatchItemOperationData::class, $retried->data);
        $this->assertSame($source->id, $retried->data->parentItemId);
        $this->assertSame($source->batch_id, AcceptanceBatch::findOrFail($retried->data->batchId)->parent_batch_id);
        Queue::assertPushed(RunAcceptanceBatch::class);
    }

    public function test_cleanup_failure_makes_retry_unsafe(): void
    {
        Queue::fake();
        $this->registerProvider(PrerequisiteSchema::none());
        $profile = Profile::factory()->for(User::factory())->create();
        $service = app(AcceptanceOperationService::class);
        $started = $service->execute(new OperationRequest('acceptance.batch.start', [
            'app' => ['app-a'],
            'profile_id' => $profile->id,
            'mode' => 'async',
        ]));
        $source = AcceptanceBatch::findOrFail($started->data->batchId)->items()->firstOrFail();
        $source->update([
            'state' => BatchItemState::FAILED,
            'cleanup_error_code' => 'cleanup_failed',
            'retryable' => true,
            'permanent' => false,
            'finished_at' => now(),
        ]);

        $retried = $service->execute(new OperationRequest('acceptance.batch.item.retry', [
            'item_id' => $source->id,
            'expected_lock_version' => $source->lock_version,
        ]));

        $this->assertSame('rejected', $retried->status);
        $this->assertSame('acceptance_retry_not_safe', $retried->errorCode);
        $this->assertDatabaseCount('acceptance_batches', 1);
    }

    public function test_post_entry_scenario_failure_cannot_be_retried_even_if_flagged_retryable(): void
    {
        Queue::fake();
        $this->registerProvider(PrerequisiteSchema::none());
        $profile = Profile::factory()->for(User::factory())->create();
        $service = app(AcceptanceOperationService::class);
        $started = $service->execute(new OperationRequest('acceptance.batch.start', [
            'app' => ['app-a'],
            'profile_id' => $profile->id,
            'mode' => 'async',
        ]));
        $batch = AcceptanceBatch::findOrFail($started->data->batchId);
        $source = $batch->items()->firstOrFail();
        $source->update([
            'state' => BatchItemState::FAILED,
            'error_code' => 'acceptance_step_failed',
            'retryable' => true,
            'permanent' => false,
            'finished_at' => now(),
        ]);
        AcceptanceExecutionAttempt::factory()->create([
            'item_id' => $source->id,
            'operation_id' => $batch->operation()->value('id'),
            'state' => AttemptState::FAILED,
            'executor_entered' => true,
            'error_code' => 'acceptance_step_failed',
            'retryable' => true,
            'permanent' => false,
            'finished_at' => now(),
        ]);

        $retried = $service->execute(new OperationRequest('acceptance.batch.item.retry', [
            'item_id' => $source->id,
            'expected_lock_version' => $source->lock_version,
        ]));

        $this->assertSame('rejected', $retried->status);
        $this->assertSame('acceptance_retry_not_safe', $retried->errorCode);
        $this->assertDatabaseCount('acceptance_batches', 1);
    }

    public function test_catalog_fingerprint_drift_stops_dispatch(): void
    {
        Bus::fake();
        $provider = $this->registerProvider(PrerequisiteSchema::none());
        $profile = Profile::factory()->for(User::factory())->create();
        $started = app(AcceptanceOperationService::class)->execute(new OperationRequest('acceptance.batch.start', [
            'app' => ['app-a'],
            'profile_id' => $profile->id,
            'mode' => 'async',
        ]));
        $provider->version = 'v2';

        try {
            app(AcceptanceBatchService::class)->dispatchWave(
                $started->data->batchId,
                $started->operationId,
            );
            $this->fail('Catalog drift must reject dispatch.');
        } catch (AcceptanceExecutionException $exception) {
            $this->assertSame('acceptance_batch_plan_changed', $exception->errorCode);
        }

        $this->assertDatabaseCount('acceptance_execution_attempts', 0);
    }

    public function test_duplicate_item_delivery_does_not_create_a_second_attempt(): void
    {
        Bus::fake();
        $this->registerProvider(PrerequisiteSchema::none());
        $profile = Profile::factory()->for(User::factory())->create();
        $started = app(AcceptanceOperationService::class)->execute(new OperationRequest('acceptance.batch.start', [
            'app' => ['app-a'],
            'profile_id' => $profile->id,
            'mode' => 'async',
        ]));
        $batches = app(AcceptanceBatchService::class);
        $batches->dispatchWave($started->data->batchId, $started->operationId);
        $attempt = AcceptanceExecutionAttempt::query()->firstOrFail();

        $batches->executeItem($attempt->item_id, $attempt->id, $started->operationId, $attempt->execution_token);
        $batches->executeItem($attempt->item_id, $attempt->id, $started->operationId, $attempt->execution_token);

        $this->assertDatabaseCount('acceptance_execution_attempts', 1);
        $this->assertSame(AttemptState::FAILED, $attempt->fresh()->state);
        $this->assertSame(1, $attempt->fresh()->infrastructure_attempts);
        $this->assertFalse($attempt->fresh()->executor_entered);
        $this->assertDatabaseCount('tests', 0);
    }

    public function test_sync_mode_uses_the_same_attempt_path_and_aggregates_failure(): void
    {
        Queue::fake();
        $this->registerProvider(PrerequisiteSchema::none());
        $profile = Profile::factory()->for(User::factory())->create();

        $result = app(AcceptanceOperationService::class)->execute(new OperationRequest('acceptance.batch.start', [
            'app' => ['app-a'],
            'profile_id' => $profile->id,
            'mode' => 'sync',
        ]));

        $this->assertSame('failed', $result->status);
        $this->assertSame(BatchState::COMPLETED_WITH_FAILURES, $result->data->batchState);
        $this->assertSame(OperationState::FAILED, $result->data->operationState);
        $this->assertSame(1, $result->data->failedCount);
        $this->assertDatabaseCount('acceptance_execution_attempts', 1);
        Queue::assertNothingPushed();
    }

    public function test_database_queue_worker_processes_a_bounded_batch_without_target_io(): void
    {
        $this->registerProvider(PrerequisiteSchema::none());
        $profile = Profile::factory()->for(User::factory())->create();
        $started = app(AcceptanceOperationService::class)->execute(new OperationRequest('acceptance.batch.start', [
            'app' => ['app-a'],
            'profile_id' => $profile->id,
            'mode' => 'async',
        ]));

        $this->assertDatabaseHas('jobs', ['queue' => 'acceptance']);
        $this->runOneAcceptanceJob();
        $this->assertDatabaseCount('acceptance_execution_attempts', 1);
        $this->runOneAcceptanceJob();
        $this->runOneAcceptanceJob();

        $this->assertDatabaseCount('jobs', 0);
        $this->assertSame(
            BatchState::COMPLETED_WITH_FAILURES,
            AcceptanceBatch::findOrFail($started->data->batchId)->state,
        );
        $this->assertDatabaseCount('tests', 0);
    }

    /** @param list<string> $variantKeys */
    private function registerProvider(PrerequisiteSchema $schema, array $variantKeys = ['variant-a']): object
    {
        $metadata = new ScenarioMetadata(
            ['browser'], ['batch'], AutomationDisposition::AUTOMATED, EvidenceMode::METADATA_ONLY,
        );
        $scenario = new class($metadata) implements AcceptanceScenario
        {
            public function __construct(private readonly ScenarioMetadata $scenarioMetadata) {}

            public function key(): string
            {
                return 'scenario-a';
            }

            public function name(): string
            {
                return 'Scenario A';
            }

            public function metadata(): ScenarioMetadata
            {
                return $this->scenarioMetadata;
            }

            public function steps(TestContext $context): iterable
            {
                return [];
            }
        };
        $provider = new class($schema, $scenario, $metadata, $variantKeys) implements AcceptanceComponentProvider
        {
            public string $version = 'v1';

            /** @param list<string> $variantKeys */
            public function __construct(
                private readonly PrerequisiteSchema $schema,
                private readonly AcceptanceScenario $scenario,
                private readonly ScenarioMetadata $metadata,
                private readonly array $variantKeys,
            ) {}

            public function key(): string
            {
                return 'app-a';
            }

            public function catalogVersion(): string
            {
                return $this->version;
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
                yield new ScenarioDescriptor('scenario-a', 'component-a', 'suite-a', $this->metadata);
            }

            public function variants(string $scenarioKey): iterable
            {
                if ($scenarioKey === 'scenario-a') {
                    foreach ($this->variantKeys as $variantKey) {
                        yield new VariantDescriptor($variantKey, $this->schema);
                    }
                }
            }

            public function resolveScenario(
                string $componentKey,
                string $suiteKey,
                string $scenarioKey,
                string $variantKey,
            ): ?AcceptanceScenario {
                return $componentKey === 'component-a' && $suiteKey === 'suite-a'
                    && $scenarioKey === 'scenario-a' && in_array($variantKey, $this->variantKeys, true)
                    ? $this->scenario : null;
            }
        };
        app(AcceptanceAppRegistry::class)->register($provider);

        return $provider;
    }

    private function runOneAcceptanceJob(): void
    {
        $exitCode = Artisan::call('queue:work', [
            'connection' => 'database',
            '--queue' => 'acceptance',
            '--once' => true,
            '--sleep' => 0,
            '--tries' => 3,
            '--timeout' => 75,
        ]);

        $this->assertSame(0, $exitCode, Artisan::output());
    }
}
