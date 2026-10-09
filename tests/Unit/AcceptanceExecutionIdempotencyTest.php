<?php

namespace Tests\Unit;

use App\Jobs\RunAcceptanceBatchItem;
use App\Models\AcceptanceBatch;
use App\Models\AcceptanceBatchItem;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptanceExecutionIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_item_identity_and_idempotency_key_are_unique_per_batch(): void
    {
        $batch = AcceptanceBatch::factory()->create();
        $item = AcceptanceBatchItem::factory()->for($batch, 'batch')->create();

        $this->expectException(QueryException::class);
        AcceptanceBatchItem::factory()->for($batch, 'batch')->create([
            'ordinal' => 1,
            'app_key' => $item->app_key,
            'component_key' => $item->component_key,
            'suite_key' => $item->suite_key,
            'scenario_key' => $item->scenario_key,
            'variant_key' => $item->variant_key,
            'idempotency_key' => $item->idempotency_key,
        ]);
    }

    public function test_item_job_payload_contains_only_execution_identifiers(): void
    {
        $job = new RunAcceptanceBatchItem(11, 12, 'operation-uuid', 'execution-token');
        $payload = serialize($job);

        $this->assertStringContainsString('operation-uuid', $payload);
        $this->assertStringContainsString('execution-token', $payload);
        $this->assertStringNotContainsString('password', $payload);
        $this->assertStringNotContainsString('selector_snapshot', $payload);
        $this->assertSame(11, $job->itemId);
        $this->assertSame(12, $job->attemptId);
    }
}
