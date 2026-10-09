<?php

namespace Tests\Unit;

use App\Acceptance\Execution\AcceptanceExecutionException;
use App\Acceptance\Execution\AcceptanceExecutionStateMachine;
use App\Acceptance\Execution\Enums\AttemptState;
use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\OperationState;
use PHPUnit\Framework\TestCase;

class AcceptanceExecutionStateMachineTest extends TestCase
{
    public function test_every_approved_transition_is_accepted(): void
    {
        $states = new AcceptanceExecutionStateMachine;
        $operation = [
            [OperationState::DRAFT, OperationState::AWAITING_INPUT],
            [OperationState::DRAFT, OperationState::AWAITING_APPROVAL],
            [OperationState::DRAFT, OperationState::READY],
            [OperationState::DRAFT, OperationState::FAILED],
            [OperationState::AWAITING_INPUT, OperationState::AWAITING_APPROVAL],
            [OperationState::AWAITING_INPUT, OperationState::READY],
            [OperationState::AWAITING_INPUT, OperationState::CANCELLING],
            [OperationState::AWAITING_INPUT, OperationState::EXPIRED],
            [OperationState::AWAITING_INPUT, OperationState::FAILED],
            [OperationState::AWAITING_APPROVAL, OperationState::AWAITING_INPUT],
            [OperationState::AWAITING_APPROVAL, OperationState::READY],
            [OperationState::AWAITING_APPROVAL, OperationState::CANCELLING],
            [OperationState::AWAITING_APPROVAL, OperationState::EXPIRED],
            [OperationState::AWAITING_APPROVAL, OperationState::FAILED],
            [OperationState::READY, OperationState::QUEUED],
            [OperationState::READY, OperationState::RUNNING],
            [OperationState::READY, OperationState::CANCELLING],
            [OperationState::READY, OperationState::FAILED],
            [OperationState::QUEUED, OperationState::RUNNING],
            [OperationState::QUEUED, OperationState::CANCELLING],
            [OperationState::QUEUED, OperationState::FAILED],
            [OperationState::RUNNING, OperationState::SUCCEEDED],
            [OperationState::RUNNING, OperationState::FAILED],
            [OperationState::RUNNING, OperationState::CANCELLING],
            [OperationState::CANCELLING, OperationState::CANCELLED],
            [OperationState::CANCELLING, OperationState::FAILED],
        ];
        foreach ($operation as [$from, $to]) {
            $states->operation($from, $to);
            $this->addToAssertionCount(1);
        }
        $states->operation(OperationState::RUNNING, OperationState::QUEUED, BatchState::INTERRUPTED);

        $batch = [
            [BatchState::PLANNED, BatchState::QUEUED], [BatchState::PLANNED, BatchState::RUNNING],
            [BatchState::PLANNED, BatchState::CANCELLING], [BatchState::PLANNED, BatchState::FAILED],
            [BatchState::QUEUED, BatchState::RUNNING], [BatchState::QUEUED, BatchState::CANCELLING],
            [BatchState::QUEUED, BatchState::INTERRUPTED], [BatchState::QUEUED, BatchState::FAILED],
            [BatchState::RUNNING, BatchState::CANCELLING], [BatchState::RUNNING, BatchState::COMPLETED],
            [BatchState::RUNNING, BatchState::COMPLETED_WITH_FAILURES], [BatchState::RUNNING, BatchState::INTERRUPTED],
            [BatchState::RUNNING, BatchState::FAILED], [BatchState::INTERRUPTED, BatchState::QUEUED],
            [BatchState::INTERRUPTED, BatchState::RUNNING], [BatchState::INTERRUPTED, BatchState::CANCELLING],
            [BatchState::INTERRUPTED, BatchState::FAILED], [BatchState::CANCELLING, BatchState::CANCELLED],
            [BatchState::CANCELLING, BatchState::FAILED],
        ];
        foreach ($batch as [$from, $to]) {
            $states->batch($from, $to);
            $this->addToAssertionCount(1);
        }

        foreach ([
            [BatchItemState::PENDING, BatchItemState::QUEUED], [BatchItemState::PENDING, BatchItemState::BLOCKED],
            [BatchItemState::PENDING, BatchItemState::SKIPPED], [BatchItemState::PENDING, BatchItemState::CANCELLED],
            [BatchItemState::BLOCKED, BatchItemState::QUEUED], [BatchItemState::BLOCKED, BatchItemState::CANCELLED],
            [BatchItemState::QUEUED, BatchItemState::RUNNING], [BatchItemState::QUEUED, BatchItemState::BLOCKED],
            [BatchItemState::QUEUED, BatchItemState::CANCELLED], [BatchItemState::RUNNING, BatchItemState::PASSED],
            [BatchItemState::RUNNING, BatchItemState::FAILED], [BatchItemState::RUNNING, BatchItemState::BLOCKED],
            [BatchItemState::RUNNING, BatchItemState::CANCELLED],
        ] as [$from, $to]) {
            $states->item($from, $to);
            $this->addToAssertionCount(1);
        }

        foreach ([
            [AttemptState::QUEUED, AttemptState::RUNNING], [AttemptState::QUEUED, AttemptState::ABANDONED],
            [AttemptState::RUNNING, AttemptState::SUCCEEDED], [AttemptState::RUNNING, AttemptState::FAILED],
            [AttemptState::RUNNING, AttemptState::ABANDONED],
        ] as [$from, $to]) {
            $states->attempt($from, $to);
            $this->addToAssertionCount(1);
        }
    }

    public function test_terminal_states_and_running_resume_without_interruption_are_rejected(): void
    {
        $states = new AcceptanceExecutionStateMachine;
        $invalid = [
            fn () => $states->operation(OperationState::RUNNING, OperationState::QUEUED, BatchState::RUNNING),
        ];
        foreach ([OperationState::SUCCEEDED, OperationState::FAILED, OperationState::CANCELLED, OperationState::EXPIRED] as $terminal) {
            $invalid[] = fn () => $states->operation($terminal, OperationState::RUNNING);
        }
        foreach ([BatchState::CANCELLED, BatchState::COMPLETED, BatchState::COMPLETED_WITH_FAILURES, BatchState::FAILED] as $terminal) {
            $invalid[] = fn () => $states->batch($terminal, BatchState::RUNNING);
        }
        foreach ([BatchItemState::PASSED, BatchItemState::FAILED, BatchItemState::SKIPPED, BatchItemState::CANCELLED] as $terminal) {
            $invalid[] = fn () => $states->item($terminal, BatchItemState::QUEUED);
        }
        foreach ([AttemptState::SUCCEEDED, AttemptState::FAILED, AttemptState::ABANDONED] as $terminal) {
            $invalid[] = fn () => $states->attempt($terminal, AttemptState::RUNNING);
        }

        foreach ($invalid as $transition) {
            try {
                $transition();
                $this->fail('Expected an invalid transition.');
            } catch (AcceptanceExecutionException $exception) {
                $this->assertSame('acceptance_batch_transition_invalid', $exception->errorCode);
            }
        }
    }
}
