<?php

namespace Tests\Feature;

use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\OperationState;
use App\Acceptance\Reporting\AcceptanceQueryService;
use App\Acceptance\Reporting\AcceptanceReportingException;
use App\Models\AcceptanceBatch;
use App\Models\AcceptanceBatchItem;
use App\Models\AcceptanceExecutionOperation;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AcceptanceQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_can_be_queried_by_operation_with_bounded_sql(): void
    {
        [$batch, $operation] = $this->persistStatusFixture();
        $queries = 0;
        DB::listen(function (QueryExecuted $query) use (&$queries): void {
            $queries++;
        });

        $status = $this->app->make(AcceptanceQueryService::class)->status(operationId: $operation->id);

        $this->assertSame($operation->id, $status->operationId);
        $this->assertSame($batch->id, $status->batchId);
        $this->assertSame(OperationState::RUNNING, $status->operationState);
        $this->assertSame(BatchState::RUNNING, $status->batchState);
        $this->assertSame(1, $status->pendingCount);
        $this->assertSame(1, $status->passedCount);
        $this->assertSame(1, $status->failedCount);
        $this->assertLessThanOrEqual(5, $queries);
    }

    public function test_status_can_be_queried_by_batch_and_rejects_ambiguous_or_missing_queries(): void
    {
        [$batch, $operation] = $this->persistStatusFixture();
        $service = $this->app->make(AcceptanceQueryService::class);

        $this->assertSame($operation->id, $service->status(batchId: $batch->id)->operationId);

        foreach ([
            fn () => $service->status(),
            fn () => $service->status($operation->id, $batch->id),
            fn () => $service->status(operationId: 'not-a-uuid'),
        ] as $query) {
            try {
                $query();
                $this->fail('Expected an invalid reporting query.');
            } catch (AcceptanceReportingException $exception) {
                $this->assertSame('acceptance_report_query_invalid', $exception->errorCode);
            }
        }

        try {
            $service->status(batchId: $batch->id + 10_000);
            $this->fail('Expected a missing report.');
        } catch (AcceptanceReportingException $exception) {
            $this->assertSame('acceptance_report_not_found', $exception->errorCode);
        }
    }

    /** @return array{AcceptanceBatch, AcceptanceExecutionOperation} */
    private function persistStatusFixture(): array
    {
        $batch = AcceptanceBatch::factory()->create([
            'state' => BatchState::RUNNING,
            'matched_count' => 4,
            'executable_count' => 3,
            'skipped_count' => 1,
            'failure_count' => 1,
        ]);
        $operation = AcceptanceExecutionOperation::factory()->for($batch, 'batch')->create([
            'state' => OperationState::RUNNING,
        ]);
        foreach ([
            BatchItemState::PENDING,
            BatchItemState::PASSED,
            BatchItemState::FAILED,
            BatchItemState::SKIPPED,
        ] as $ordinal => $state) {
            AcceptanceBatchItem::factory()->for($batch, 'batch')->create([
                'ordinal' => $ordinal,
                'scenario_key' => 'scenario-'.$ordinal,
                'idempotency_key' => hash('sha256', 'item-'.$ordinal),
                'state' => $state,
            ]);
        }

        return [$batch, $operation];
    }
}
