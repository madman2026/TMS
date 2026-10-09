<?php

namespace Tests\Unit;

use App\Acceptance\Execution\AcceptanceRecoveryService;
use App\Acceptance\Execution\Enums\AttemptState;
use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\OperationState;
use App\Models\AcceptanceBatch;
use App\Models\AcceptanceBatchItem;
use App\Models\AcceptanceExecutionAttempt;
use App\Models\AcceptanceExecutionOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptanceRecoveryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_attempt_before_executor_entry_is_blocked_for_safe_resume(): void
    {
        [$batch, $operation, $item, $attempt] = $this->staleExecution(false);

        $this->assertSame(1, app(AcceptanceRecoveryService::class)->reconcile($batch->id));

        $this->assertSame(AttemptState::ABANDONED, $attempt->fresh()->state);
        $this->assertSame(BatchItemState::BLOCKED, $item->fresh()->state);
        $this->assertTrue($item->fresh()->retryable);
        $this->assertFalse($item->fresh()->permanent);
        $this->assertSame(BatchState::INTERRUPTED, $batch->fresh()->state);
        $this->assertSame('acceptance_attempt_stale', $operation->fresh()->error_code);
    }

    public function test_stale_attempt_after_executor_entry_is_a_permanent_ambiguous_failure(): void
    {
        [$batch, , $item, $attempt] = $this->staleExecution(true);

        $this->assertSame(1, app(AcceptanceRecoveryService::class)->reconcile($batch->id));

        $this->assertSame(AttemptState::ABANDONED, $attempt->fresh()->state);
        $this->assertSame(BatchItemState::FAILED, $item->fresh()->state);
        $this->assertFalse($item->fresh()->retryable);
        $this->assertTrue($item->fresh()->permanent);
        $this->assertTrue($item->fresh()->admin_action_required);
    }

    /**
     * @return array{AcceptanceBatch, AcceptanceExecutionOperation, AcceptanceBatchItem, AcceptanceExecutionAttempt}
     */
    private function staleExecution(bool $executorEntered): array
    {
        $batch = AcceptanceBatch::factory()->create(['state' => BatchState::RUNNING]);
        $operation = AcceptanceExecutionOperation::factory()->for($batch, 'batch')->create([
            'state' => OperationState::RUNNING,
        ]);
        $item = AcceptanceBatchItem::factory()->for($batch, 'batch')->create([
            'state' => $executorEntered ? BatchItemState::RUNNING : BatchItemState::QUEUED,
        ]);
        $attempt = AcceptanceExecutionAttempt::factory()->create([
            'item_id' => $item->id,
            'operation_id' => $operation->id,
            'state' => $executorEntered ? AttemptState::RUNNING : AttemptState::QUEUED,
            'executor_entered' => $executorEntered,
            'lease_expires_at' => now()->subSecond(),
        ]);

        return [$batch, $operation, $item, $attempt];
    }
}
