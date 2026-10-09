<?php

namespace Database\Factories;

use App\Acceptance\Execution\Enums\AttemptState;
use App\Models\AcceptanceBatchItem;
use App\Models\AcceptanceExecutionAttempt;
use App\Models\AcceptanceExecutionOperation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AcceptanceExecutionAttempt>
 */
class AcceptanceExecutionAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => AcceptanceBatchItem::factory(),
            'operation_id' => AcceptanceExecutionOperation::factory(),
            'test_id' => null,
            'attempt_number' => 1,
            'execution_token' => (string) Str::uuid(),
            'state' => AttemptState::QUEUED,
            'executor_capability' => 'browser',
            'infrastructure_attempts' => 0,
            'executor_entered' => false,
            'queued_at' => now(),
        ];
    }
}
