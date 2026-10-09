<?php

namespace Tests\Feature;

use App\Acceptance\Execution\Enums\AttemptState;
use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\ExecutionMode;
use App\Acceptance\Execution\Enums\OperationState;
use App\Models\AcceptanceBatch;
use App\Models\AcceptanceBatchItem;
use App\Models\AcceptanceExecutionAttempt;
use App\Models\AcceptanceExecutionOperation;
use App\Models\Profile;
use App\Models\User;
use App\TestStatusEnum;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcceptanceBatchPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_history_relations_casts_and_test_nullification_are_persisted(): void
    {
        $profile = Profile::factory()->for(User::factory())->create();
        $batch = AcceptanceBatch::factory()->for($profile)->create([
            'mode' => ExecutionMode::ASYNC,
            'state' => BatchState::RUNNING,
        ]);
        $operation = AcceptanceExecutionOperation::factory()->for($batch, 'batch')->create([
            'state' => OperationState::RUNNING,
        ]);
        $item = AcceptanceBatchItem::factory()->for($batch, 'batch')->create([
            'state' => BatchItemState::RUNNING,
        ]);
        $test = $profile->tests()->create([
            'name' => 'Pack 13 persistence',
            'status' => TestStatusEnum::FINISHED,
            'app_key' => $item->app_key,
            'component_key' => $item->component_key,
            'suite_key' => $item->suite_key,
            'scenario_key' => $item->scenario_key,
            'variant_key' => $item->variant_key,
        ]);
        $attempt = AcceptanceExecutionAttempt::factory()->create([
            'item_id' => $item->id,
            'operation_id' => $operation->id,
            'test_id' => $test->id,
            'state' => AttemptState::SUCCEEDED,
            'executor_entered' => true,
            'finished_at' => now(),
        ]);

        $this->assertTrue(Schema::hasColumns('acceptance_execution_attempts', [
            'item_id', 'operation_id', 'test_id', 'execution_token', 'lease_expires_at',
        ]));
        $this->assertSame(ExecutionMode::ASYNC, $batch->fresh()->mode);
        $this->assertSame(BatchState::RUNNING, $item->fresh()->batch->state);
        $this->assertTrue($batch->fresh()->operation->is($operation));
        $this->assertTrue($item->fresh()->attempts->first()->is($attempt));

        $test->delete();

        $this->assertNull($attempt->fresh()->test_id);
        $this->expectException(QueryException::class);
        $batch->delete();
    }

    public function test_attempt_number_is_unique_within_an_item(): void
    {
        $batch = AcceptanceBatch::factory()->create();
        $operation = AcceptanceExecutionOperation::factory()->for($batch, 'batch')->create();
        $item = AcceptanceBatchItem::factory()->for($batch, 'batch')->create();
        AcceptanceExecutionAttempt::factory()->create([
            'item_id' => $item->id,
            'operation_id' => $operation->id,
            'attempt_number' => 1,
        ]);

        $this->expectException(QueryException::class);
        AcceptanceExecutionAttempt::factory()->create([
            'item_id' => $item->id,
            'operation_id' => $operation->id,
            'attempt_number' => 1,
        ]);
    }
}
