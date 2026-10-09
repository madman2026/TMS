<?php

namespace App\Acceptance\Execution;

use App\Acceptance\Execution\Enums\AttemptState;
use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\OperationState;

final class AcceptanceExecutionStateMachine
{
    /** @var array<string, list<string>> */
    private const OPERATION_TRANSITIONS = [
        'draft' => ['awaiting_input', 'awaiting_approval', 'ready', 'failed'],
        'awaiting_input' => ['awaiting_approval', 'ready', 'cancelling', 'expired', 'failed'],
        'awaiting_approval' => ['awaiting_input', 'ready', 'cancelling', 'expired', 'failed'],
        'ready' => ['queued', 'running', 'cancelling', 'failed'],
        'queued' => ['running', 'cancelling', 'failed'],
        'running' => ['queued', 'succeeded', 'failed', 'cancelling'],
        'cancelling' => ['cancelled', 'failed'],
    ];

    /** @var array<string, list<string>> */
    private const BATCH_TRANSITIONS = [
        'planned' => ['queued', 'running', 'cancelling', 'failed'],
        'queued' => ['running', 'cancelling', 'interrupted', 'failed'],
        'running' => ['cancelling', 'completed', 'completed_with_failures', 'interrupted', 'failed'],
        'interrupted' => ['queued', 'running', 'cancelling', 'failed'],
        'cancelling' => ['cancelled', 'failed'],
    ];

    /** @var array<string, list<string>> */
    private const ITEM_TRANSITIONS = [
        'pending' => ['queued', 'blocked', 'skipped', 'cancelled'],
        'blocked' => ['queued', 'cancelled'],
        'queued' => ['running', 'blocked', 'cancelled'],
        'running' => ['passed', 'failed', 'blocked', 'cancelled'],
    ];

    /** @var array<string, list<string>> */
    private const ATTEMPT_TRANSITIONS = [
        'queued' => ['running', 'abandoned'],
        'running' => ['succeeded', 'failed', 'abandoned'],
    ];

    public function operation(OperationState $from, OperationState $to, ?BatchState $batchState = null): void
    {
        if ($from === OperationState::RUNNING && $to === OperationState::QUEUED
            && $batchState !== BatchState::INTERRUPTED) {
            throw AcceptanceExecutionException::because('acceptance_batch_transition_invalid');
        }

        $this->assertTransition(self::OPERATION_TRANSITIONS, $from->value, $to->value);
    }

    public function batch(BatchState $from, BatchState $to): void
    {
        $this->assertTransition(self::BATCH_TRANSITIONS, $from->value, $to->value);
    }

    public function item(BatchItemState $from, BatchItemState $to): void
    {
        $this->assertTransition(self::ITEM_TRANSITIONS, $from->value, $to->value);
    }

    public function attempt(AttemptState $from, AttemptState $to): void
    {
        $this->assertTransition(self::ATTEMPT_TRANSITIONS, $from->value, $to->value);
    }

    /** @param array<string, list<string>> $transitions */
    private function assertTransition(array $transitions, string $from, string $to): void
    {
        if (! in_array($to, $transitions[$from] ?? [], true)) {
            throw AcceptanceExecutionException::because('acceptance_batch_transition_invalid');
        }
    }
}
