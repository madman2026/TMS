<?php

namespace Database\Factories;

use App\Acceptance\Execution\Enums\OperationState;
use App\Models\AcceptanceBatch;
use App\Models\AcceptanceExecutionOperation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AcceptanceExecutionOperation>
 */
class AcceptanceExecutionOperationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'batch_id' => AcceptanceBatch::factory(),
            'correlation_id' => (string) Str::uuid(),
            'origin' => 'start',
            'state' => OperationState::DRAFT,
            'lock_version' => 0,
            'admin_action_required' => false,
        ];
    }
}
