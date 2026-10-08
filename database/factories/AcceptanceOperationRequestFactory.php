<?php

namespace Database\Factories;

use App\Acceptance\Prerequisites\Data\PrerequisiteSchema;
use App\Acceptance\Prerequisites\Enums\PrerequisiteState;
use App\Models\AcceptanceOperationRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<AcceptanceOperationRequest> */
final class AcceptanceOperationRequestFactory extends Factory
{
    protected $model = AcceptanceOperationRequest::class;

    public function definition(): array
    {
        $schema = PrerequisiteSchema::none();

        return [
            'id' => (string) Str::uuid(),
            'correlation_id' => (string) Str::uuid(),
            'app_key' => 'example-app',
            'component_key' => 'example-component',
            'suite_key' => 'example-suite',
            'scenario_key' => 'example-scenario',
            'variant_key' => 'example-variant',
            'profile_id' => Profile::factory()->for(User::factory()),
            'schema_version' => $schema->version,
            'schema_fingerprint' => $schema->fingerprint(),
            'state' => PrerequisiteState::READY,
            'lock_version' => 0,
            'expires_at' => now()->addSeconds($schema->requestTtlSeconds),
            'cancelled_at' => null,
        ];
    }
}
