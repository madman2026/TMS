<?php

namespace Database\Factories;

use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\ExecutionMode;
use App\Data\AcceptancePlan;
use App\Models\AcceptanceBatch;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AcceptanceBatch>
 */
class AcceptanceBatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_batch_id' => null,
            'profile_id' => Profile::factory()->for(User::factory()),
            'correlation_id' => (string) Str::uuid(),
            'mode' => ExecutionMode::SYNC,
            'version' => AcceptancePlan::VERSION,
            'plan_fingerprint' => hash('sha256', fake()->uuid()),
            'selector_snapshot' => ['app' => ['example-app'], 'limit' => 1],
            'catalog_versions' => ['example-app' => 'v1'],
            'state' => BatchState::PLANNED,
            'max_failures' => 0,
        ];
    }
}
