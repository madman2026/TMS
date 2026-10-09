<?php

namespace App\Acceptance\Execution;

use App\Acceptance\Execution\Data\BatchItemOperationData;
use App\Acceptance\Execution\Data\BatchOperationData;
use App\Acceptance\Execution\Data\BatchPrerequisiteReference;
use App\Acceptance\Execution\Enums\AttemptState;
use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\ExecutionMode;
use App\Acceptance\Execution\Enums\OperationState;
use App\Acceptance\Operations\Data\RunOperationData;
use App\Acceptance\Prerequisites\PrerequisiteException;
use App\Acceptance\Prerequisites\PrerequisiteService;
use App\Acceptance\Targets\Data\TargetResourceLifecycleData;
use App\Data\AcceptancePlan;
use App\Data\AcceptanceSelector;
use App\Data\ScenarioDescriptor;
use App\Events\AcceptanceExecutionStateChanged;
use App\Jobs\RunAcceptanceBatch;
use App\Jobs\RunAcceptanceBatchItem;
use App\Models\AcceptanceBatch;
use App\Models\AcceptanceBatchItem;
use App\Models\AcceptanceExecutionAttempt;
use App\Models\AcceptanceExecutionOperation;
use App\Models\Profile;
use App\Services\AcceptancePlanner;
use App\Services\AcceptanceVariantDispatcher;
use App\TestStatusEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\ExecutorTraceContext;
use Modules\Core\Data\RunOptions;
use Throwable;

final class AcceptanceBatchService
{
    public function __construct(
        private readonly AcceptancePlanner $planner,
        private readonly AcceptanceVariantDispatcher $dispatcher,
        private readonly PrerequisiteService $prerequisites,
        private readonly AcceptanceExecutionService $execution,
        private readonly AcceptanceExecutionStateMachine $states,
    ) {}

    /** @param list<BatchPrerequisiteReference> $references */
    public function start(
        AcceptanceSelector $selector,
        int $profileId,
        ExecutionMode $mode,
        array $references,
        string $correlationId,
        string $operationId,
    ): BatchOperationData {
        $this->assertConfiguration();
        $profile = Profile::query()->find($profileId)
            ?? throw AcceptanceExecutionException::because('acceptance_profile_not_found');
        $plan = $this->planner->plan($selector);
        $executableCount = count(array_filter($plan->items, fn ($item): bool => $item->executable()));
        if ($executableCount === 0) {
            throw AcceptanceExecutionException::because('acceptance_batch_empty');
        }
        $referenceMap = $this->referenceMap($references);

        $batch = DB::transaction(function () use (
            $profile,
            $plan,
            $mode,
            $correlationId,
            $operationId,
            $executableCount,
        ): AcceptanceBatch {
            $batch = AcceptanceBatch::query()->create([
                'profile_id' => $profile->getKey(),
                'correlation_id' => $correlationId,
                'mode' => $mode,
                'version' => AcceptancePlan::VERSION,
                'plan_fingerprint' => $plan->fingerprint,
                'selector_snapshot' => $plan->selector->normalized(),
                'catalog_versions' => $plan->catalogVersions,
                'state' => BatchState::PLANNED,
                'matched_count' => count($plan->items),
                'executable_count' => $executableCount,
                'skipped_count' => count($plan->items) - $executableCount,
                'max_failures' => $this->configInt('queue.max_failures'),
            ]);
            $batch->operation()->create([
                'id' => $operationId,
                'correlation_id' => $correlationId,
                'origin' => 'start',
                'state' => OperationState::DRAFT,
            ]);

            return $batch;
        }, 3);

        $blockedCodes = [];
        try {
            foreach (array_chunk($plan->items, $this->configInt('queue.dispatch_chunk'), true) as $chunk) {
                DB::transaction(function () use (
                    $batch,
                    $chunk,
                    $referenceMap,
                    $profileId,
                    $correlationId,
                    $plan,
                    &$blockedCodes,
                ): void {
                    $lockedBatch = AcceptanceBatch::query()->lockForUpdate()->findOrFail($batch->getKey());
                    foreach ($chunk as $ordinal => $planItem) {
                        $identity = $planItem->identity();
                        $reference = $referenceMap[$identity] ?? null;
                        [$state, $requestId, $errorCode] = $planItem->executable()
                            ? $this->prerequisiteDisposition($planItem->toArray(), $profileId, $reference, $correlationId)
                            : [BatchItemState::SKIPPED, null, null];
                        if ($errorCode !== null) {
                            $blockedCodes[] = $errorCode;
                        }
                        $lockedBatch->items()->create([
                            'acceptance_operation_request_id' => $requestId,
                            'ordinal' => $ordinal,
                            'app_key' => $planItem->appKey,
                            'component_key' => $planItem->componentKey,
                            'suite_key' => $planItem->suiteKey,
                            'scenario_key' => $planItem->scenarioKey,
                            'variant_key' => $planItem->variantKey,
                            'capability' => 'browser',
                            'catalog_version' => $plan->catalogVersions[$planItem->appKey],
                            'classification_snapshot' => ScenarioDescriptor::classification($planItem->metadata),
                            'idempotency_key' => hash('sha256', $plan->fingerprint."\0".$profileId."\0".$identity),
                            'state' => $state,
                            'error_code' => $errorCode,
                        ]);
                    }
                }, 3);
            }
        } catch (Throwable $exception) {
            $this->failMaterialization((int) $batch->getKey());

            throw $exception;
        }

        DB::transaction(function () use ($batch, $blockedCodes): void {
            $lockedBatch = AcceptanceBatch::query()->lockForUpdate()->findOrFail($batch->getKey());
            $operation = $lockedBatch->operation()->lockForUpdate()->firstOrFail();
            $operation->state = $this->waitingOperationState($blockedCodes);
            $operation->lock_version++;
            $operation->save();
            $this->emitState(
                $lockedBatch,
                $operation,
                'operation',
                OperationState::DRAFT->value,
                $operation->state->value,
            );
        }, 3);

        if ($batch->operation()->firstOrFail()->state === OperationState::READY) {
            $this->begin($batch);
        }

        return $this->snapshot($batch->fresh());
    }

    /** @param list<BatchPrerequisiteReference> $references */
    public function resume(
        int $batchId,
        string $operationId,
        int $expectedLockVersion,
        array $references,
    ): BatchOperationData {
        $this->assertConfiguration();
        $this->assertPlanCurrent($this->batchOrFail($batchId));
        $referenceMap = $this->referenceMap($references);

        $batch = DB::transaction(function () use (
            $batchId,
            $operationId,
            $expectedLockVersion,
            $referenceMap,
        ): AcceptanceBatch {
            $batch = AcceptanceBatch::query()->lockForUpdate()->find($batchId)
                ?? throw AcceptanceExecutionException::because('acceptance_batch_not_found');
            $operation = AcceptanceExecutionOperation::query()->lockForUpdate()
                ->whereKey($operationId)->where('batch_id', $batchId)->first()
                ?? throw AcceptanceExecutionException::because('acceptance_batch_not_found');
            if ($batch->lock_version !== $expectedLockVersion || $batch->state->terminal()) {
                throw AcceptanceExecutionException::because('acceptance_batch_conflict');
            }

            $blockedCodes = [];
            $items = $batch->items()->where('state', BatchItemState::BLOCKED)->lockForUpdate()->get();
            foreach ($items as $item) {
                $reference = $referenceMap[$this->itemIdentity($item)] ?? null;
                [$state, $requestId, $errorCode] = $this->prerequisiteDisposition(
                    $this->itemIdentityArray($item),
                    (int) $batch->profile_id,
                    $reference,
                    $batch->correlation_id,
                );
                $item->update([
                    'acceptance_operation_request_id' => $requestId,
                    'state' => $state,
                    'error_code' => $errorCode,
                    'lock_version' => $item->lock_version + 1,
                ]);
                if ($errorCode !== null) {
                    $blockedCodes[] = $errorCode;
                }
            }
            if ($blockedCodes !== []) {
                $target = $this->waitingOperationState($blockedCodes);
                if ($operation->state !== $target) {
                    $this->states->operation($operation->state, $target);
                    $operation->update(['state' => $target, 'lock_version' => $operation->lock_version + 1]);
                }
                $batch->increment('lock_version');

                return $batch->refresh();
            }

            if ($batch->state === BatchState::INTERRUPTED) {
                $this->states->batch($batch->state, BatchState::QUEUED);
                $this->states->operation($operation->state, OperationState::QUEUED, BatchState::INTERRUPTED);
                $batch->update([
                    'state' => BatchState::QUEUED,
                    'interrupted_at' => null,
                    'lock_version' => $batch->lock_version + 1,
                ]);
                $operation->update([
                    'state' => OperationState::QUEUED,
                    'queued_at' => now(),
                    'lock_version' => $operation->lock_version + 1,
                ]);

                return $batch->refresh();
            }

            if ($operation->state !== OperationState::READY) {
                $this->states->operation($operation->state, OperationState::READY);
                $operation->update(['state' => OperationState::READY, 'lock_version' => $operation->lock_version + 1]);
            }
            $batch->increment('lock_version');

            return $batch->refresh();
        }, 3);

        if ($batch->items()->where('state', BatchItemState::BLOCKED)->doesntExist()) {
            if ($batch->state === BatchState::QUEUED) {
                $this->dispatchOrchestrator($batch);
            } else {
                $this->begin($batch);
            }
        }

        return $this->snapshot($batch->fresh());
    }

    public function retry(int $itemId, int $expectedLockVersion, string $correlationId, string $operationId): BatchItemOperationData
    {
        $this->assertConfiguration();
        $source = AcceptanceBatchItem::query()->with(['batch', 'attempts'])->find($itemId)
            ?? throw AcceptanceExecutionException::because('acceptance_attempt_not_found');
        if ($source->lock_version !== $expectedLockVersion || $source->state !== BatchItemState::FAILED
            || $source->retryable !== true || $source->cleanup_error_code !== null
            || ($source->attempts->contains('executor_entered', true)
                && ! in_array($source->error_code, ['acceptance_browser_start_failed', 'http_transport_failed'], true))) {
            throw AcceptanceExecutionException::because('acceptance_retry_not_safe');
        }
        $this->assertPlanCurrent($source->batch);

        $childItem = DB::transaction(function () use ($source, $correlationId, $operationId): AcceptanceBatchItem {
            $source = AcceptanceBatchItem::query()->with('batch')->lockForUpdate()->findOrFail($source->getKey());
            $batch = AcceptanceBatch::query()->create([
                'parent_batch_id' => $source->batch_id,
                'profile_id' => $source->batch->profile_id,
                'correlation_id' => $correlationId,
                'mode' => $source->batch->mode,
                'version' => $source->batch->version,
                'plan_fingerprint' => $source->batch->plan_fingerprint,
                'selector_snapshot' => $source->batch->selector_snapshot,
                'catalog_versions' => $source->batch->catalog_versions,
                'state' => BatchState::PLANNED,
                'matched_count' => 1,
                'executable_count' => 1,
                'skipped_count' => 0,
                'max_failures' => $source->batch->max_failures,
            ]);
            $batch->operation()->create([
                'id' => $operationId,
                'correlation_id' => $correlationId,
                'origin' => 'retry',
                'state' => OperationState::READY,
            ]);

            return $batch->items()->create([
                'acceptance_operation_request_id' => $source->acceptance_operation_request_id,
                'retry_of_item_id' => $source->getKey(),
                'ordinal' => 0,
                'app_key' => $source->app_key,
                'component_key' => $source->component_key,
                'suite_key' => $source->suite_key,
                'scenario_key' => $source->scenario_key,
                'variant_key' => $source->variant_key,
                'capability' => $source->capability,
                'catalog_version' => $source->catalog_version,
                'classification_snapshot' => $source->classification_snapshot,
                'idempotency_key' => hash('sha256', $source->idempotency_key."\0retry\0".$operationId),
                'state' => BatchItemState::PENDING,
            ]);
        }, 3);

        $this->begin($childItem->batch);
        $childItem->refresh();
        $attempt = $childItem->attempts()->latest('id')->first();

        return $this->itemSnapshot($childItem, $attempt);
    }

    public function cancel(int $batchId, string $operationId, int $expectedLockVersion): BatchOperationData
    {
        $batch = DB::transaction(function () use ($batchId, $operationId, $expectedLockVersion): AcceptanceBatch {
            $batch = AcceptanceBatch::query()->lockForUpdate()->find($batchId)
                ?? throw AcceptanceExecutionException::because('acceptance_batch_not_found');
            $operation = AcceptanceExecutionOperation::query()->lockForUpdate()
                ->whereKey($operationId)->where('batch_id', $batchId)->first()
                ?? throw AcceptanceExecutionException::because('acceptance_batch_not_found');
            if ($batch->state === BatchState::CANCELLED) {
                return $batch;
            }
            if ($batch->lock_version !== $expectedLockVersion || $batch->state->terminal()) {
                throw AcceptanceExecutionException::because('acceptance_batch_conflict');
            }

            $this->states->batch($batch->state, BatchState::CANCELLING);
            $this->states->operation($operation->state, OperationState::CANCELLING, $batch->state);
            $previousBatchState = $batch->state->value;
            $previousOperationState = $operation->state->value;
            $now = now();
            $batch->update([
                'state' => BatchState::CANCELLING,
                'cancel_requested_at' => $now,
                'lock_version' => $batch->lock_version + 1,
            ]);
            $operation->update([
                'state' => OperationState::CANCELLING,
                'cancel_requested_at' => $now,
                'lock_version' => $operation->lock_version + 1,
            ]);
            $this->emitState($batch, $operation, 'batch', $previousBatchState, BatchState::CANCELLING->value);
            $this->emitState($batch, $operation, 'operation', $previousOperationState, OperationState::CANCELLING->value);
            $batch->items()->whereIn('state', [
                BatchItemState::PENDING->value,
                BatchItemState::QUEUED->value,
                BatchItemState::BLOCKED->value,
            ])->update([
                'state' => BatchItemState::CANCELLED->value,
                'error_code' => 'acceptance_batch_cancelled',
                'finished_at' => $now,
                'updated_at' => $now,
            ]);

            return $batch->refresh();
        }, 3);

        $this->finalize($batchId);

        return $this->snapshot($batch->fresh());
    }

    public function dispatchWave(int $batchId, string $operationId): void
    {
        $batch = $this->batchOrFail($batchId);
        if ($batch->operation()->value('id') !== $operationId || $batch->state->terminal()) {
            return;
        }
        $this->assertPlanCurrent($batch);
        $lock = Cache::lock('acceptance:batch:dispatch:'.$batchId, $this->configInt('queue.lock_seconds'));
        if (! $lock->get()) {
            return;
        }

        try {
            $attempts = $this->prepareAttempts($batchId);
            if ($attempts->isEmpty()) {
                $this->finalize($batchId);

                return;
            }
            $jobs = $attempts->map(fn (AcceptanceExecutionAttempt $attempt): RunAcceptanceBatchItem => new RunAcceptanceBatchItem(
                (int) $attempt->item_id,
                (int) $attempt->getKey(),
                $operationId,
                $attempt->execution_token,
            ))->all();
            $laravelBatch = Bus::batch($jobs)
                ->name('acceptance:'.$batchId)
                ->allowFailures()
                ->onConnection($this->queueConnection())
                ->onQueue($this->queueName())
                ->dispatch();
            AcceptanceBatch::query()->whereKey($batchId)->update(['laravel_batch_id' => $laravelBatch->id]);
        } catch (Throwable) {
            $this->interrupt($batchId, 'acceptance_batch_dispatch_failed');
        } finally {
            $lock->release();
        }
    }

    public function executeItem(int $itemId, int $attemptId, string $operationId, string $executionToken): void
    {
        $lock = Cache::lock('acceptance:item:'.$itemId, $this->configInt('queue.lock_seconds'), $executionToken);
        if (! $lock->get()) {
            return;
        }

        try {
            $context = $this->claimAttempt($itemId, $attemptId, $operationId, $executionToken);
            if ($context === null) {
                return;
            }
            [$item, $attempt, $batch, $profile] = $context;
            $identity = new AcceptanceExecutionIdentity(
                $item->app_key,
                $item->component_key,
                $item->suite_key,
                $item->scenario_key,
                $item->variant_key,
            );
            $scenario = $this->dispatcher->resolve($identity);
            $prerequisites = $this->prerequisites->executionData(
                $this->itemIdentityArray($item),
                (int) $profile->getKey(),
                $item->acceptance_operation_request_id,
                $batch->correlation_id,
                'acceptance.batch.execute',
            );
            $result = $this->execution->execute(
                $profile,
                $identity,
                $scenario,
                $this->runOptions(),
                $prerequisites,
                $operationId,
                $batch->correlation_id,
                new ExecutorTraceContext(
                    $batch->correlation_id,
                    $operationId,
                    (int) $batch->getKey(),
                    (int) $item->getKey(),
                    (int) $attempt->getKey(),
                ),
                fn (): bool => AcceptanceBatch::query()->whereKey($batch->getKey())
                    ->whereNotNull('cancel_requested_at')->exists(),
                fn (): bool => $this->markExecutorEntered((int) $attempt->getKey(), $attempt->execution_token),
            );
            $this->recordResult($itemId, $attemptId, $result);
        } catch (PrerequisiteException $exception) {
            $this->recordFailure($itemId, $attemptId, $exception->errorCode, false, true, false);
        } catch (Throwable) {
            $this->recordFailure($itemId, $attemptId, 'acceptance_execution_persistence_failed', true, false, true);
        } finally {
            $lock->release();
            $batchId = $this->batchIdForItem($itemId);
            if (AcceptanceBatch::query()->findOrFail($batchId)->mode === ExecutionMode::ASYNC) {
                RunAcceptanceBatch::dispatch($batchId, $operationId)
                    ->onConnection($this->queueConnection())->onQueue($this->queueName())->afterCommit();
            }
        }
    }

    public function failAttempt(int $itemId, int $attemptId, string $errorCode): void
    {
        $this->recordFailure($itemId, $attemptId, $errorCode, true, false, true);
    }

    public function failDispatch(int $batchId): void
    {
        $this->interrupt($batchId, 'acceptance_batch_dispatch_failed');
    }

    private function begin(AcceptanceBatch $batch): void
    {
        if ($batch->mode === ExecutionMode::SYNC) {
            $this->markStarted($batch, BatchState::RUNNING, OperationState::RUNNING);
            while (true) {
                $attempt = $this->prepareAttempts((int) $batch->getKey(), 1)->first();
                if (! $attempt instanceof AcceptanceExecutionAttempt) {
                    break;
                }
                $this->executeItem(
                    (int) $attempt->item_id,
                    (int) $attempt->getKey(),
                    (string) $batch->operation()->value('id'),
                    $attempt->execution_token,
                );
            }
            $this->finalize((int) $batch->getKey());

            return;
        }

        $this->markStarted($batch, BatchState::QUEUED, OperationState::QUEUED);
        $this->dispatchOrchestrator($batch->fresh());
    }

    private function markStarted(AcceptanceBatch $batch, BatchState $batchState, OperationState $operationState): void
    {
        DB::transaction(function () use ($batch, $batchState, $operationState): void {
            $lockedBatch = AcceptanceBatch::query()->lockForUpdate()->findOrFail($batch->getKey());
            $operation = $lockedBatch->operation()->lockForUpdate()->firstOrFail();
            if ($lockedBatch->state !== BatchState::PLANNED) {
                return;
            }
            $this->states->batch($lockedBatch->state, $batchState);
            $this->states->operation($operation->state, $operationState);
            $previousBatchState = $lockedBatch->state->value;
            $previousOperationState = $operation->state->value;
            $now = now();
            $lockedBatch->update([
                'state' => $batchState,
                'started_at' => $now,
                'lock_version' => $lockedBatch->lock_version + 1,
            ]);
            $operation->update([
                'state' => $operationState,
                'queued_at' => $batchState === BatchState::QUEUED ? $now : null,
                'started_at' => $batchState === BatchState::RUNNING ? $now : null,
                'lock_version' => $operation->lock_version + 1,
            ]);
            $this->emitState($lockedBatch, $operation, 'batch', $previousBatchState, $batchState->value);
            $this->emitState($lockedBatch, $operation, 'operation', $previousOperationState, $operationState->value);
        }, 3);
    }

    /** @return Collection<int, AcceptanceExecutionAttempt> */
    private function prepareAttempts(int $batchId, ?int $forcedLimit = null): Collection
    {
        return DB::transaction(function () use ($batchId, $forcedLimit): Collection {
            $batch = AcceptanceBatch::query()->lockForUpdate()->find($batchId)
                ?? throw AcceptanceExecutionException::because('acceptance_batch_not_found');
            if ($batch->state->terminal() || $batch->state === BatchState::CANCELLING) {
                return new Collection;
            }
            $inFlight = $batch->items()->whereIn('state', [
                BatchItemState::QUEUED->value,
                BatchItemState::RUNNING->value,
            ])->count();
            $available = max(0, $this->configInt('queue.max_in_flight') - $inFlight);
            $limit = min($forcedLimit ?? $available, $available);
            if ($limit === 0) {
                return new Collection;
            }
            $items = $batch->items()->where('state', BatchItemState::PENDING)
                ->orderBy('ordinal')->limit($limit)->lockForUpdate()->get();
            $attempts = new Collection;
            foreach ($items as $item) {
                $this->states->item($item->state, BatchItemState::QUEUED);
                $previousItemState = $item->state->value;
                $attemptNumber = $item->attempt_count + 1;
                $item->update([
                    'state' => BatchItemState::QUEUED,
                    'attempt_count' => $attemptNumber,
                    'queued_at' => now(),
                    'lock_version' => $item->lock_version + 1,
                ]);
                $attempts->push($item->attempts()->create([
                    'operation_id' => $batch->operation()->value('id'),
                    'attempt_number' => $attemptNumber,
                    'execution_token' => (string) Str::uuid(),
                    'state' => AttemptState::QUEUED,
                    'executor_capability' => $item->capability,
                    'queued_at' => now(),
                    'lease_expires_at' => now()->addSeconds($this->configInt('queue.stale_after_seconds')),
                ]));
                $this->emitState(
                    $batch,
                    $batch->operation()->firstOrFail(),
                    'item',
                    $previousItemState,
                    BatchItemState::QUEUED->value,
                    $item,
                    $attempts->last(),
                );
            }
            if ($items->isNotEmpty()) {
                $batch->update([
                    'next_dispatch_ordinal' => ((int) $items->last()->ordinal) + 1,
                    'lock_version' => $batch->lock_version + 1,
                ]);
            }

            return $attempts;
        }, 3);
    }

    /** @return array{AcceptanceBatchItem, AcceptanceExecutionAttempt, AcceptanceBatch, Profile}|null */
    private function claimAttempt(int $itemId, int $attemptId, string $operationId, string $executionToken): ?array
    {
        return DB::transaction(function () use ($itemId, $attemptId, $operationId, $executionToken): ?array {
            $item = AcceptanceBatchItem::query()->lockForUpdate()->find($itemId)
                ?? throw AcceptanceExecutionException::because('acceptance_attempt_not_found');
            $attempt = AcceptanceExecutionAttempt::query()->lockForUpdate()
                ->whereKey($attemptId)->where('item_id', $itemId)->where('operation_id', $operationId)
                ->where('execution_token', $executionToken)->first()
                ?? throw AcceptanceExecutionException::because('acceptance_attempt_not_found');
            if ($attempt->state->terminal() || $item->state->terminal()) {
                return null;
            }
            $batch = AcceptanceBatch::query()->lockForUpdate()->findOrFail($item->batch_id);
            if ($batch->cancel_requested_at !== null) {
                $this->states->item($item->state, BatchItemState::CANCELLED);
                $this->states->attempt($attempt->state, AttemptState::ABANDONED);
                $item->update(['state' => BatchItemState::CANCELLED, 'error_code' => 'acceptance_batch_cancelled', 'finished_at' => now()]);
                $attempt->update(['state' => AttemptState::ABANDONED, 'error_code' => 'acceptance_batch_cancelled', 'finished_at' => now()]);

                return null;
            }
            $this->states->item($item->state, BatchItemState::RUNNING);
            $this->states->attempt($attempt->state, AttemptState::RUNNING);
            $previousItemState = $item->state->value;
            $previousAttemptState = $attempt->state->value;
            $now = now();
            $item->update(['state' => BatchItemState::RUNNING, 'started_at' => $now, 'lock_version' => $item->lock_version + 1]);
            $attempt->update([
                'state' => AttemptState::RUNNING,
                'infrastructure_attempts' => $attempt->infrastructure_attempts + 1,
                'executor_entered' => false,
                'started_at' => $now,
                'heartbeat_at' => $now,
                'lease_expires_at' => $now->copy()->addSeconds($this->configInt('queue.stale_after_seconds')),
            ]);
            if ($batch->state === BatchState::QUEUED) {
                $previousBatchState = $batch->state->value;
                $this->states->batch($batch->state, BatchState::RUNNING);
                $batch->update(['state' => BatchState::RUNNING]);
                $operation = $batch->operation()->lockForUpdate()->firstOrFail();
                $previousOperationState = $operation->state->value;
                if ($operation->state === OperationState::QUEUED) {
                    $this->states->operation($operation->state, OperationState::RUNNING);
                    $operation->update(['state' => OperationState::RUNNING, 'started_at' => $now]);
                }
                $this->emitState($batch, $operation, 'batch', $previousBatchState, BatchState::RUNNING->value);
                $this->emitState($batch, $operation, 'operation', $previousOperationState, $operation->state->value);
            }
            $operation ??= $batch->operation()->firstOrFail();
            $this->emitState($batch, $operation, 'item', $previousItemState, BatchItemState::RUNNING->value, $item, $attempt);
            $this->emitState($batch, $operation, 'attempt', $previousAttemptState, AttemptState::RUNNING->value, $item, $attempt);
            $profile = Profile::query()->findOrFail($batch->profile_id);

            return [$item->refresh(), $attempt->refresh(), $batch->refresh(), $profile];
        }, 3);
    }

    private function markExecutorEntered(int $attemptId, string $executionToken): bool
    {
        return AcceptanceExecutionAttempt::query()
            ->whereKey($attemptId)
            ->where('execution_token', $executionToken)
            ->where('state', AttemptState::RUNNING->value)
            ->update([
                'executor_entered' => true,
                'heartbeat_at' => now(),
                'updated_at' => now(),
            ]) === 1;
    }

    private function recordResult(
        int $itemId,
        int $attemptId,
        RunOperationData|TargetResourceLifecycleData $result,
    ): void {
        $lifecycle = $result instanceof RunOperationData ? $result->resources : $result;
        $passed = $result instanceof RunOperationData && $result->testStatus === TestStatusEnum::FINISHED
            && $lifecycle->status === 'succeeded';
        $cancelled = $lifecycle->status === 'cancelled';
        $errorCode = $lifecycle->primaryErrorCode ?? $lifecycle->cleanupErrorCode
            ?? ($passed ? null : 'acceptance_command_failed');
        $execution = $lifecycle->execution;
        [$retryable, $permanent, $adminActionRequired] = $execution === null
            ? $this->failureClassification($errorCode)
            : [$execution->retryable, $execution->permanent, $execution->adminActionRequired];
        $this->recordOutcome(
            $itemId,
            $attemptId,
            $passed ? BatchItemState::PASSED : ($cancelled ? BatchItemState::CANCELLED : BatchItemState::FAILED),
            $passed ? AttemptState::SUCCEEDED : AttemptState::FAILED,
            $errorCode,
            $lifecycle->cleanupErrorCode,
            $passed ? null : $retryable,
            $passed ? null : $permanent,
            $passed ? false : $adminActionRequired,
            $result instanceof RunOperationData ? $result->testId : null,
        );
    }

    private function recordFailure(
        int $itemId,
        int $attemptId,
        string $errorCode,
        bool $retryable,
        bool $permanent,
        bool $adminActionRequired,
    ): void {
        $this->recordOutcome(
            $itemId,
            $attemptId,
            BatchItemState::FAILED,
            AttemptState::FAILED,
            $errorCode,
            null,
            $retryable,
            $permanent,
            $adminActionRequired,
            null,
        );
    }

    private function recordOutcome(
        int $itemId,
        int $attemptId,
        BatchItemState $itemState,
        AttemptState $attemptState,
        ?string $errorCode,
        ?string $cleanupErrorCode,
        ?bool $retryable,
        ?bool $permanent,
        bool $adminActionRequired,
        ?int $testId,
    ): void {
        $batchId = DB::transaction(function () use (
            $itemId,
            $attemptId,
            $itemState,
            $attemptState,
            $errorCode,
            $cleanupErrorCode,
            $retryable,
            $permanent,
            $adminActionRequired,
            $testId,
        ): int {
            $item = AcceptanceBatchItem::query()->lockForUpdate()->findOrFail($itemId);
            $attempt = AcceptanceExecutionAttempt::query()->lockForUpdate()->findOrFail($attemptId);
            if ($item->state->terminal() || $attempt->state->terminal()) {
                return (int) $item->batch_id;
            }
            $this->states->item($item->state, $itemState);
            $this->states->attempt($attempt->state, $attemptState);
            $previousItemState = $item->state->value;
            $previousAttemptState = $attempt->state->value;
            $now = now();
            $item->update([
                'state' => $itemState,
                'error_code' => $errorCode,
                'cleanup_error_code' => $cleanupErrorCode,
                'retryable' => $retryable,
                'permanent' => $permanent,
                'admin_action_required' => $adminActionRequired,
                'finished_at' => $now,
                'lock_version' => $item->lock_version + 1,
            ]);
            $attempt->update([
                'state' => $attemptState,
                'test_id' => $testId,
                'executor_key' => $attempt->executor_entered ? 'browser' : null,
                'error_code' => $errorCode,
                'cleanup_error_code' => $cleanupErrorCode,
                'retryable' => $retryable,
                'permanent' => $permanent,
                'admin_action_required' => $adminActionRequired,
                'heartbeat_at' => $now,
                'finished_at' => $now,
            ]);
            if ($itemState === BatchItemState::FAILED) {
                AcceptanceBatch::query()->whereKey($item->batch_id)->increment('failure_count');
            }
            $batch = $item->batch()->firstOrFail();
            $operation = $batch->operation()->firstOrFail();
            $this->emitState($batch, $operation, 'item', $previousItemState, $itemState->value, $item, $attempt);
            $this->emitState($batch, $operation, 'attempt', $previousAttemptState, $attemptState->value, $item, $attempt);

            return (int) $item->batch_id;
        }, 3);
        $this->finalize($batchId);
    }

    private function finalize(int $batchId): void
    {
        DB::transaction(function () use ($batchId): void {
            $batch = AcceptanceBatch::query()->lockForUpdate()->find($batchId);
            if ($batch === null || $batch->state->terminal()) {
                return;
            }
            $operation = $batch->operation()->lockForUpdate()->firstOrFail();
            $active = $batch->items()->whereIn('state', [
                BatchItemState::PENDING->value,
                BatchItemState::QUEUED->value,
                BatchItemState::RUNNING->value,
                BatchItemState::BLOCKED->value,
            ])->count();
            if ($batch->state === BatchState::CANCELLING) {
                if ($batch->items()->where('state', BatchItemState::RUNNING)->exists()) {
                    return;
                }
                $this->states->batch($batch->state, BatchState::CANCELLED);
                $this->states->operation($operation->state, OperationState::CANCELLED);
                $previousBatchState = $batch->state->value;
                $previousOperationState = $operation->state->value;
                $batch->update(['state' => BatchState::CANCELLED, 'finished_at' => now(), 'lock_version' => $batch->lock_version + 1]);
                $operation->update(['state' => OperationState::CANCELLED, 'finished_at' => now(), 'lock_version' => $operation->lock_version + 1]);
                $this->emitState($batch, $operation, 'batch', $previousBatchState, BatchState::CANCELLED->value);
                $this->emitState($batch, $operation, 'operation', $previousOperationState, OperationState::CANCELLED->value);

                return;
            }
            if ($active > 0) {
                $this->applyFailureThreshold($batch, $operation);

                return;
            }
            $failed = $batch->items()->where('state', BatchItemState::FAILED)->count();
            $targetBatch = $failed > 0 ? BatchState::COMPLETED_WITH_FAILURES : BatchState::COMPLETED;
            $targetOperation = $failed > 0 ? OperationState::FAILED : OperationState::SUCCEEDED;
            $this->states->batch($batch->state, $targetBatch);
            $this->states->operation($operation->state, $targetOperation);
            $previousBatchState = $batch->state->value;
            $previousOperationState = $operation->state->value;
            $batch->update([
                'state' => $targetBatch,
                'failure_count' => $failed,
                'finished_at' => now(),
                'lock_version' => $batch->lock_version + 1,
            ]);
            $operation->update([
                'state' => $targetOperation,
                'error_code' => $failed > 0 ? 'acceptance_command_failed' : null,
                'retryable' => $failed > 0 ? false : null,
                'permanent' => $failed > 0 ? true : null,
                'finished_at' => now(),
                'lock_version' => $operation->lock_version + 1,
            ]);
            $this->emitState($batch, $operation, 'batch', $previousBatchState, $targetBatch->value);
            $this->emitState($batch, $operation, 'operation', $previousOperationState, $targetOperation->value);
        }, 3);
    }

    private function applyFailureThreshold(AcceptanceBatch $batch, AcceptanceExecutionOperation $operation): void
    {
        if ($batch->max_failures === 0 || $batch->failure_count < $batch->max_failures) {
            return;
        }
        $now = now();
        $batch->items()->whereIn('state', [BatchItemState::PENDING->value, BatchItemState::QUEUED->value])
            ->update(['state' => BatchItemState::CANCELLED->value, 'error_code' => 'acceptance_batch_cancelled', 'finished_at' => $now]);
        if ($batch->items()->where('state', BatchItemState::RUNNING)->exists()) {
            $this->states->batch($batch->state, BatchState::CANCELLING);
            $this->states->operation($operation->state, OperationState::CANCELLING);
            $batch->update(['state' => BatchState::CANCELLING, 'cancel_requested_at' => $now]);
            $operation->update(['state' => OperationState::CANCELLING, 'cancel_requested_at' => $now]);

            return;
        }
        $this->states->batch($batch->state, BatchState::FAILED);
        $this->states->operation($operation->state, OperationState::FAILED);
        $batch->update(['state' => BatchState::FAILED, 'finished_at' => $now]);
        $operation->update(['state' => OperationState::FAILED, 'error_code' => 'acceptance_command_failed', 'finished_at' => $now]);
    }

    private function interrupt(int $batchId, string $errorCode): void
    {
        DB::transaction(function () use ($batchId, $errorCode): void {
            $batch = AcceptanceBatch::query()->lockForUpdate()->find($batchId);
            if ($batch === null || ! in_array($batch->state, [BatchState::QUEUED, BatchState::RUNNING], true)) {
                return;
            }
            $this->states->batch($batch->state, BatchState::INTERRUPTED);
            $batch->update(['state' => BatchState::INTERRUPTED, 'interrupted_at' => now()]);
            $batch->operation()->update([
                'error_code' => $errorCode,
                'retryable' => true,
                'permanent' => false,
                'admin_action_required' => true,
            ]);
        }, 3);
    }

    private function failMaterialization(int $batchId): void
    {
        DB::transaction(function () use ($batchId): void {
            $batch = AcceptanceBatch::query()->lockForUpdate()->find($batchId);
            if ($batch === null || $batch->state !== BatchState::PLANNED) {
                return;
            }
            $operation = $batch->operation()->lockForUpdate()->firstOrFail();
            if ($operation->state !== OperationState::DRAFT) {
                return;
            }
            $this->states->batch($batch->state, BatchState::FAILED);
            $this->states->operation($operation->state, OperationState::FAILED);
            $batch->update([
                'state' => BatchState::FAILED,
                'finished_at' => now(),
                'lock_version' => $batch->lock_version + 1,
            ]);
            $operation->update([
                'state' => OperationState::FAILED,
                'error_code' => 'acceptance_execution_persistence_failed',
                'retryable' => true,
                'permanent' => false,
                'admin_action_required' => true,
                'finished_at' => now(),
                'lock_version' => $operation->lock_version + 1,
            ]);
            $this->emitState($batch, $operation, 'batch', BatchState::PLANNED->value, BatchState::FAILED->value);
            $this->emitState($batch, $operation, 'operation', OperationState::DRAFT->value, OperationState::FAILED->value);
        }, 3);
    }

    private function dispatchOrchestrator(AcceptanceBatch $batch): void
    {
        RunAcceptanceBatch::dispatch((int) $batch->getKey(), (string) $batch->operation()->value('id'))
            ->onConnection($this->queueConnection())->onQueue($this->queueName())->afterCommit();
    }

    /** @param array<string, mixed> $identity @return array{BatchItemState, ?string, ?string} */
    private function prerequisiteDisposition(
        array $identity,
        int $profileId,
        ?BatchPrerequisiteReference $reference,
        string $correlationId,
    ): array {
        try {
            $data = $this->prerequisites->executionData(
                [
                    'app_key' => $identity['app_key'],
                    'component_key' => $identity['component_key'],
                    'suite_key' => $identity['suite_key'],
                    'scenario_key' => $identity['scenario_key'],
                    'variant_key' => $identity['variant_key'],
                ],
                $profileId,
                $reference?->requestId,
                $correlationId,
                'acceptance.batch.start',
            );

            return [BatchItemState::PENDING, $data->requestId, null];
        } catch (PrerequisiteException $exception) {
            if (! in_array($exception->errorCode, ['input_required', 'approval_required'], true)) {
                throw $exception;
            }

            return [BatchItemState::BLOCKED, $reference?->requestId, $exception->errorCode];
        }
    }

    /** @param list<string> $blockedCodes */
    private function waitingOperationState(array $blockedCodes): OperationState
    {
        if (in_array('input_required', $blockedCodes, true)) {
            return OperationState::AWAITING_INPUT;
        }
        if (in_array('approval_required', $blockedCodes, true)) {
            return OperationState::AWAITING_APPROVAL;
        }

        return OperationState::READY;
    }

    /** @param list<BatchPrerequisiteReference> $references @return array<string, BatchPrerequisiteReference> */
    private function referenceMap(array $references): array
    {
        $map = [];
        foreach ($references as $reference) {
            if (! $reference instanceof BatchPrerequisiteReference || isset($map[$reference->identity()])) {
                throw AcceptanceExecutionException::because('operation_request_invalid');
            }
            $map[$reference->identity()] = $reference;
        }

        return $map;
    }

    private function assertPlanCurrent(AcceptanceBatch $batch): void
    {
        $selector = $this->selectorFromSnapshot($batch->selector_snapshot);
        if ($this->planner->plan($selector)->fingerprint !== $batch->plan_fingerprint) {
            throw AcceptanceExecutionException::because('acceptance_batch_plan_changed');
        }
    }

    /** @param array<string, mixed> $snapshot */
    private function selectorFromSnapshot(array $snapshot): AcceptanceSelector
    {
        return new AcceptanceSelector(
            apps: $snapshot['app'] ?? [],
            scenarios: $snapshot['scenario'] ?? [],
            variants: $snapshot['variant'] ?? [],
            components: $snapshot['component'] ?? [],
            suites: $snapshot['suite'] ?? [],
            capabilities: $snapshot['capability'] ?? [],
            tags: $snapshot['tag'] ?? [],
            dispositions: $snapshot['disposition'] ?? [],
            evidenceModes: $snapshot['evidence_mode'] ?? [],
            limit: $snapshot['limit'] ?? 1000,
        );
    }

    private function snapshot(AcceptanceBatch $batch): BatchOperationData
    {
        $operation = $batch->operation()->firstOrFail();
        $counts = $batch->items()->selectRaw('state, COUNT(*) as aggregate')->groupBy('state')
            ->pluck('aggregate', 'state')->map(fn ($count): int => (int) $count)->all();

        return new BatchOperationData(
            (string) $operation->getKey(),
            (int) $batch->getKey(),
            $batch->correlation_id,
            $operation->state,
            $batch->state,
            $batch->mode,
            $batch->plan_fingerprint,
            $batch->lock_version,
            $batch->matched_count,
            $batch->executable_count,
            $batch->skipped_count,
            $counts[BatchItemState::PENDING->value] ?? 0,
            $counts[BatchItemState::BLOCKED->value] ?? 0,
            $counts[BatchItemState::QUEUED->value] ?? 0,
            $counts[BatchItemState::RUNNING->value] ?? 0,
            $counts[BatchItemState::PASSED->value] ?? 0,
            $counts[BatchItemState::FAILED->value] ?? 0,
            $counts[BatchItemState::CANCELLED->value] ?? 0,
            $operation->error_code,
            $operation->retryable,
            $operation->permanent,
            $operation->admin_action_required,
        );
    }

    private function itemSnapshot(AcceptanceBatchItem $item, ?AcceptanceExecutionAttempt $attempt): BatchItemOperationData
    {
        return new BatchItemOperationData(
            (string) $item->batch->operation()->value('id'),
            (int) $item->batch_id,
            (int) $item->getKey(),
            $item->retry_of_item_id,
            $attempt?->getKey(),
            $attempt?->test_id,
            $item->state,
            $item->app_key,
            $item->component_key,
            $item->suite_key,
            $item->scenario_key,
            $item->variant_key,
            $item->error_code,
            $item->retryable,
            $item->permanent,
            $item->admin_action_required,
        );
    }

    private function runOptions(): RunOptions
    {
        return new RunOptions(
            browser: (string) config('core.acceptance.browser', 'chromium'),
            headless: (bool) config('core.acceptance.headless', true),
            timeoutMs: $this->configInt('execution.executor_timeout_ms'),
            slowMoMs: (int) config('core.acceptance.slow_mo_ms', 0),
        );
    }

    private function assertConfiguration(): void
    {
        $values = [
            'queue.dispatch_chunk' => [1, 1000],
            'queue.max_in_flight' => [1, 32],
            'queue.job_timeout_seconds' => [45, 900],
            'queue.infrastructure_attempts' => [1, 5],
            'queue.lock_seconds' => [80, 1200],
            'queue.stale_after_seconds' => [90, 3600],
            'queue.max_failures' => [0, 1000],
            'execution.executor_timeout_ms' => [1000, 30000],
        ];
        foreach ($values as $key => [$minimum, $maximum]) {
            $value = $this->configInt($key);
            if ($value < $minimum || $value > $maximum) {
                throw AcceptanceExecutionException::because('acceptance_configuration_invalid');
            }
        }
        $connection = config('queue.connections.'.$this->queueConnection());
        $retryAfter = is_array($connection) ? ($connection['retry_after'] ?? null) : null;
        if (! is_array($connection)
            || ! is_int($retryAfter)
            || $retryAfter <= $this->configInt('queue.job_timeout_seconds')
            || $this->configInt('queue.lock_seconds') <= $this->configInt('queue.job_timeout_seconds')
            || $this->configInt('queue.stale_after_seconds') <= $this->configInt('queue.lock_seconds')) {
            throw AcceptanceExecutionException::because('acceptance_configuration_invalid');
        }
    }

    private function configInt(string $key): int
    {
        return (int) config('acceptance.'.$key);
    }

    private function queueConnection(): string
    {
        return (string) config('acceptance.queue.connection');
    }

    private function queueName(): string
    {
        return (string) config('acceptance.queue.name');
    }

    private function batchOrFail(int $batchId): AcceptanceBatch
    {
        return AcceptanceBatch::query()->find($batchId)
            ?? throw AcceptanceExecutionException::because('acceptance_batch_not_found');
    }

    private function batchIdForItem(int $itemId): int
    {
        return (int) (AcceptanceBatchItem::query()->whereKey($itemId)->value('batch_id')
            ?? throw AcceptanceExecutionException::because('acceptance_attempt_not_found'));
    }

    /** @return array{app_key: string, component_key: string, suite_key: string, scenario_key: string, variant_key: string} */
    private function itemIdentityArray(AcceptanceBatchItem $item): array
    {
        return [
            'app_key' => $item->app_key,
            'component_key' => $item->component_key,
            'suite_key' => $item->suite_key,
            'scenario_key' => $item->scenario_key,
            'variant_key' => $item->variant_key,
        ];
    }

    private function itemIdentity(AcceptanceBatchItem $item): string
    {
        return implode("\0", array_values($this->itemIdentityArray($item)));
    }

    /** @return array{bool, bool, bool} */
    private function failureClassification(?string $errorCode): array
    {
        return match ($errorCode) {
            'target_not_ready', 'acceptance_batch_dispatch_failed', 'acceptance_attempt_timeout' => [true, false, false],
            'fixture_setup_failed', 'cleanup_failed', 'acceptance_browser_start_failed',
            'acceptance_result_persistence_failed', 'http_transport_failed' => [true, false, true],
            'resource_unavailable' => [false, true, true],
            default => [false, true, false],
        };
    }

    private function emitState(
        AcceptanceBatch $batch,
        AcceptanceExecutionOperation $operation,
        string $entity,
        string $previousState,
        string $currentState,
        ?AcceptanceBatchItem $item = null,
        ?AcceptanceExecutionAttempt $attempt = null,
    ): void {
        AcceptanceExecutionStateChanged::dispatch(
            $batch->correlation_id,
            (string) $operation->getKey(),
            (int) $batch->getKey(),
            $item?->getKey(),
            $attempt?->getKey(),
            $attempt?->test_id,
            $entity,
            $previousState,
            $currentState,
            $attempt?->error_code ?? $item?->error_code ?? $operation->error_code,
            $attempt?->retryable ?? $item?->retryable ?? $operation->retryable,
            $attempt?->permanent ?? $item?->permanent ?? $operation->permanent,
            (bool) ($attempt?->admin_action_required ?? $item?->admin_action_required ?? $operation->admin_action_required),
        );
    }
}
