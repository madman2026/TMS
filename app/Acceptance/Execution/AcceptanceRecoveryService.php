<?php

namespace App\Acceptance\Execution;

use App\Acceptance\Execution\Enums\AttemptState;
use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Execution\Enums\BatchState;
use App\Events\AcceptanceExecutionStateChanged;
use App\Models\AcceptanceBatch;
use App\Models\AcceptanceBatchItem;
use App\Models\AcceptanceExecutionAttempt;
use App\Models\AcceptanceExecutionOperation;
use Illuminate\Support\Facades\DB;

final class AcceptanceRecoveryService
{
    public function __construct(private readonly AcceptanceExecutionStateMachine $states) {}

    public function reconcile(?int $batchId = null): int
    {
        $query = AcceptanceExecutionAttempt::query()
            ->whereIn('state', [AttemptState::QUEUED->value, AttemptState::RUNNING->value])
            ->where('lease_expires_at', '<=', now())
            ->orderBy('id');
        if ($batchId !== null) {
            $query->whereHas('item', fn ($items) => $items->where('batch_id', $batchId));
        }
        $ids = $query->pluck('id');
        $reconciled = 0;
        foreach ($ids as $id) {
            $reconciled += DB::transaction(function () use ($id): int {
                $attempt = AcceptanceExecutionAttempt::query()->lockForUpdate()->find($id);
                if ($attempt === null || $attempt->state->terminal() || $attempt->lease_expires_at?->isFuture()) {
                    return 0;
                }
                $item = AcceptanceBatchItem::query()->lockForUpdate()->findOrFail($attempt->item_id);
                $batch = AcceptanceBatch::query()->lockForUpdate()->findOrFail($item->batch_id);
                $this->states->attempt($attempt->state, AttemptState::ABANDONED);
                $targetItem = $attempt->executor_entered ? BatchItemState::FAILED : BatchItemState::BLOCKED;
                $this->states->item($item->state, $targetItem);
                $previousAttemptState = $attempt->state->value;
                $previousItemState = $item->state->value;
                $attempt->update([
                    'state' => AttemptState::ABANDONED,
                    'error_code' => 'acceptance_attempt_stale',
                    'retryable' => ! $attempt->executor_entered,
                    'permanent' => $attempt->executor_entered,
                    'admin_action_required' => true,
                    'heartbeat_at' => now(),
                    'finished_at' => now(),
                ]);
                $item->update([
                    'state' => $targetItem,
                    'error_code' => 'acceptance_attempt_stale',
                    'retryable' => ! $attempt->executor_entered,
                    'permanent' => $attempt->executor_entered,
                    'admin_action_required' => true,
                    'finished_at' => $attempt->executor_entered ? now() : null,
                    'lock_version' => $item->lock_version + 1,
                ]);
                $operation = $batch->operation()->firstOrFail();
                $this->emitState($batch, $operation, $item, $attempt, 'attempt', $previousAttemptState, AttemptState::ABANDONED->value);
                $this->emitState($batch, $operation, $item, $attempt, 'item', $previousItemState, $targetItem->value);
                if (in_array($batch->state, [BatchState::QUEUED, BatchState::RUNNING], true)) {
                    $previousBatchState = $batch->state->value;
                    $this->states->batch($batch->state, BatchState::INTERRUPTED);
                    $batch->update([
                        'state' => BatchState::INTERRUPTED,
                        'interrupted_at' => now(),
                        'reconciled_at' => now(),
                        'lock_version' => $batch->lock_version + 1,
                    ]);
                    $batch->operation()->update([
                        'error_code' => 'acceptance_attempt_stale',
                        'retryable' => true,
                        'permanent' => false,
                        'admin_action_required' => true,
                    ]);
                    $operation->refresh();
                    $this->emitState($batch, $operation, $item, $attempt, 'batch', $previousBatchState, BatchState::INTERRUPTED->value);
                }

                return 1;
            }, 3);
        }

        return $reconciled;
    }

    private function emitState(
        AcceptanceBatch $batch,
        AcceptanceExecutionOperation $operation,
        AcceptanceBatchItem $item,
        AcceptanceExecutionAttempt $attempt,
        string $entity,
        string $previousState,
        string $currentState,
    ): void {
        AcceptanceExecutionStateChanged::dispatch(
            $batch->correlation_id,
            (string) $operation->getKey(),
            (int) $batch->getKey(),
            (int) $item->getKey(),
            (int) $attempt->getKey(),
            $attempt->test_id,
            $entity,
            $previousState,
            $currentState,
            $attempt->error_code,
            $attempt->retryable,
            $attempt->permanent,
            (bool) $attempt->admin_action_required,
        );
    }
}
