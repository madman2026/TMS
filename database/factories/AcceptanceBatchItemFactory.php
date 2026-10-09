<?php

namespace Database\Factories;

use App\Acceptance\Execution\Enums\BatchItemState;
use App\Models\AcceptanceBatch;
use App\Models\AcceptanceBatchItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcceptanceBatchItem>
 */
class AcceptanceBatchItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $identity = ['example-app', 'example-component', 'example-suite', fake()->unique()->slug(2), 'default'];

        return [
            'batch_id' => AcceptanceBatch::factory(),
            'acceptance_operation_request_id' => null,
            'retry_of_item_id' => null,
            'ordinal' => 0,
            'app_key' => $identity[0],
            'component_key' => $identity[1],
            'suite_key' => $identity[2],
            'scenario_key' => $identity[3],
            'variant_key' => $identity[4],
            'capability' => 'browser',
            'catalog_version' => 'v1',
            'classification_snapshot' => [
                'capabilities' => ['browser'],
                'tags' => ['synthetic'],
                'disposition' => 'automated',
                'evidence_mode' => 'runtime',
            ],
            'idempotency_key' => hash('sha256', implode("\0", $identity)),
            'state' => BatchItemState::PENDING,
        ];
    }
}
