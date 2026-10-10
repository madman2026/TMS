<?php

namespace Tests\Feature;

use App\Acceptance\Execution\Enums\AttemptState;
use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\OperationState;
use App\Acceptance\Reporting\AcceptanceEvidenceService;
use App\Acceptance\Reporting\AcceptanceReportingException;
use App\Acceptance\Reporting\AcceptanceReportService;
use App\Acceptance\Reporting\Data\EvidenceMetadataInput;
use App\Acceptance\Reporting\Enums\EvidenceType;
use App\Models\AcceptanceBatch;
use App\Models\AcceptanceBatchItem;
use App\Models\AcceptanceExecutionAttempt;
use App\Models\AcceptanceExecutionOperation;
use App\Models\Test;
use App\TestStatusEnum;
use DateTimeImmutable;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AcceptanceReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_pages_items_attempts_and_evidence_with_bounded_sql_and_safe_fields(): void
    {
        [$batch, $first, $second, $attempt, $retry, $test] = $this->persistReportFixture();
        $this->app->make(AcceptanceEvidenceService::class)->record($attempt->id, $this->evidenceInput());
        $queries = 0;
        DB::listen(function (QueryExecuted $query) use (&$queries): void {
            $queries++;
        });
        $service = $this->app->make(AcceptanceReportService::class);

        $page = $service->report($batch->id, limit: 1);

        $this->assertTrue($page->hasMore);
        $this->assertSame($first->id, $page->items[0]->id);
        $this->assertSame($first->id, $page->nextItemId);
        $this->assertSame($attempt->id, $page->items[0]->attempts[0]->id);
        $this->assertSame($retry->id, $page->items[0]->attempts[1]->id);
        $this->assertSame([1, 2], array_column($page->items[0]->attempts, 'attemptNumber'));
        $this->assertSame($test->id, $page->items[0]->attempts[0]->testId);
        $this->assertSame('evidence-report-1', $page->items[0]->attempts[0]->evidence[0]->referenceKey);
        $this->assertLessThanOrEqual(10, $queries);
        $serialized = print_r($page, true);
        foreach ([
            'sensitive-path',
            'sensitive-test-name',
            'sensitive-test-data',
            'sensitive-step-name',
            'sensitive-step-description',
            'sensitive-step-data',
        ] as $sentinel) {
            $this->assertStringNotContainsString($sentinel, $serialized);
        }

        $next = $service->report($batch->id, $page->nextItemId, 1);
        $this->assertFalse($next->hasMore);
        $this->assertSame($second->id, $next->items[0]->id);
    }

    public function test_report_rejects_foreign_cursors_and_enforces_the_export_ceiling(): void
    {
        [$batch] = $this->persistReportFixture();
        $otherBatch = AcceptanceBatch::factory()->create();
        $other = AcceptanceBatchItem::factory()->for($otherBatch, 'batch')->create();
        $service = $this->app->make(AcceptanceReportService::class);

        try {
            $service->report($batch->id, $other->id, 1);
            $this->fail('Expected an invalid report cursor.');
        } catch (AcceptanceReportingException $exception) {
            $this->assertSame('acceptance_report_query_invalid', $exception->errorCode);
        }

        config()->set('acceptance.reporting.max_export_rows', 1);
        try {
            $service->export($batch->id);
            $this->fail('Expected the export ceiling to be enforced.');
        } catch (AcceptanceReportingException $exception) {
            $this->assertSame('acceptance_export_limit_exceeded', $exception->errorCode);
        }
    }

    /** @return array{AcceptanceBatch, AcceptanceBatchItem, AcceptanceBatchItem, AcceptanceExecutionAttempt, AcceptanceExecutionAttempt, Test} */
    private function persistReportFixture(): array
    {
        $batch = AcceptanceBatch::factory()->create([
            'state' => BatchState::RUNNING,
            'matched_count' => 2,
            'executable_count' => 2,
        ]);
        $operation = AcceptanceExecutionOperation::factory()->for($batch, 'batch')->create([
            'state' => OperationState::RUNNING,
        ]);
        $first = AcceptanceBatchItem::factory()->for($batch, 'batch')->create([
            'ordinal' => 0,
            'scenario_key' => 'scenario-first',
            'idempotency_key' => hash('sha256', 'report-first'),
            'classification_snapshot' => ['opaque' => 'sensitive-path'],
            'state' => BatchItemState::RUNNING,
            'attempt_count' => 2,
        ]);
        $second = AcceptanceBatchItem::factory()->for($batch, 'batch')->create([
            'ordinal' => 1,
            'scenario_key' => 'scenario-second',
            'idempotency_key' => hash('sha256', 'report-second'),
            'state' => BatchItemState::PENDING,
        ]);
        $test = $batch->profile->tests()->create([
            'name' => 'sensitive-test-name',
            'status' => TestStatusEnum::FINISHED,
            'data' => ['private' => 'sensitive-test-data'],
            'app_key' => $first->app_key,
            'component_key' => $first->component_key,
            'suite_key' => $first->suite_key,
            'scenario_key' => $first->scenario_key,
            'variant_key' => $first->variant_key,
        ]);
        $test->steps()->create([
            'name' => 'sensitive-step-name',
            'duration' => '1',
            'description' => 'sensitive-step-description',
            'data' => ['private' => 'sensitive-step-data'],
        ]);
        $attempt = AcceptanceExecutionAttempt::factory()->create([
            'item_id' => $first->id,
            'operation_id' => $operation->id,
            'test_id' => $test->id,
            'state' => AttemptState::RUNNING,
            'executor_entered' => true,
        ]);
        $retry = AcceptanceExecutionAttempt::factory()->create([
            'item_id' => $first->id,
            'operation_id' => $operation->id,
            'attempt_number' => 2,
            'state' => AttemptState::QUEUED,
        ]);

        return [$batch, $first, $second, $attempt, $retry, $test];
    }

    private function evidenceInput(): EvidenceMetadataInput
    {
        return new EvidenceMetadataInput(
            EvidenceType::SCREENSHOT,
            'evidence-report-1',
            str_repeat('a', 64),
            1_024,
            'image/png',
            1280,
            720,
            null,
            new DateTimeImmutable('2026-10-10T08:00:00+00:00'),
        );
    }
}
