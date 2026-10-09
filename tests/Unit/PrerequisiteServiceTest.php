<?php

namespace Tests\Unit;

use App\Acceptance\Prerequisites\Data\ApprovalRequirement;
use App\Acceptance\Prerequisites\Data\InputRequirement;
use App\Acceptance\Prerequisites\Data\PrerequisiteSchema;
use App\Acceptance\Prerequisites\Enums\InputSensitivity;
use App\Acceptance\Prerequisites\Enums\InputSource;
use App\Acceptance\Prerequisites\Enums\InputType;
use App\Acceptance\Prerequisites\Enums\PrerequisiteState;
use App\Acceptance\Prerequisites\LocalCliOperatorContextProvider;
use App\Acceptance\Prerequisites\PrerequisiteException;
use App\Acceptance\Prerequisites\PrerequisiteService;
use App\Contracts\AcceptanceComponentProvider;
use App\Data\ComponentDescriptor;
use App\Data\ScenarioDescriptor;
use App\Data\SuiteDescriptor;
use App\Data\VariantDescriptor;
use App\Models\AcceptanceOperationApproval;
use App\Models\AcceptanceOperationInput;
use App\Models\Profile;
use App\Models\User;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Tests\TestCase;

class PrerequisiteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_fingerprint_is_deterministic_and_contradictory_schemas_are_rejected(): void
    {
        $first = $this->schema();
        $second = $this->schema();

        $this->assertSame($first->fingerprint(), $second->fingerprint());
        $this->assertSame(64, strlen($first->fingerprint()));
        $allowlistA = new PrerequisiteSchema('allowlist-v1', [
            new InputRequirement('mode', InputType::STRING, allowedValues: ['beta', 'alpha']),
        ]);
        $allowlistB = new PrerequisiteSchema('allowlist-v1', [
            new InputRequirement('mode', InputType::STRING, allowedValues: ['alpha', 'beta']),
        ]);
        $this->assertSame($allowlistA->fingerprint(), $allowlistB->fingerprint());

        $this->expectException(InvalidArgumentException::class);
        new InputRequirement(
            'credential',
            InputType::SECRET_REFERENCE,
            InputSensitivity::SECRET_REFERENCE,
            allowedSources: [InputSource::LITERAL],
        );
    }

    public function test_schema_rejects_duplicate_unknown_and_inapplicable_constraints(): void
    {
        $invalidSchemas = [
            fn (): PrerequisiteSchema => new PrerequisiteSchema('v1', [
                new InputRequirement('duplicate', InputType::STRING),
                new InputRequirement('duplicate', InputType::STRING),
            ]),
            fn (): PrerequisiteSchema => new PrerequisiteSchema('v1', [], [
                new ApprovalRequirement('duplicate'),
                new ApprovalRequirement('duplicate'),
            ]),
            fn (): PrerequisiteSchema => new PrerequisiteSchema('v1', [], [
                new ApprovalRequirement('deploy', invalidatedByInputKeys: ['missing']),
            ]),
            fn (): PrerequisiteSchema => new PrerequisiteSchema('v1', [
                new InputRequirement('username', InputType::STRING),
            ], [
                new ApprovalRequirement('deploy', invalidatedByInputKeys: ['username']),
            ]),
            fn (): PrerequisiteSchema => new PrerequisiteSchema('v1', approvals: [
                new ApprovalRequirement('deploy', ttlSeconds: 301),
            ], requestTtlSeconds: 300),
            fn (): PrerequisiteSchema => new PrerequisiteSchema('v1', [
                new InputRequirement('enabled', InputType::BOOLEAN, minimum: 1),
            ]),
            fn (): PrerequisiteSchema => new PrerequisiteSchema('v1', [
                new InputRequirement('mode', InputType::STRING, allowedValues: []),
            ]),
            fn (): PrerequisiteSchema => new PrerequisiteSchema('v1', [
                new InputRequirement('mode', InputType::STRING, allowedValues: ['same', 'same']),
            ]),
            fn (): PrerequisiteSchema => new PrerequisiteSchema('v1', [
                new InputRequirement('count', InputType::INTEGER, minimum: 2, maximum: 1),
            ]),
            fn (): PrerequisiteSchema => new PrerequisiteSchema('v1', [
                new InputRequirement('name', InputType::STRING, listLimit: 1),
            ]),
            fn (): PrerequisiteSchema => new PrerequisiteSchema('Invalid Version'),
        ];

        foreach ($invalidSchemas as $schema) {
            try {
                $schema();
                $this->fail('Expected invalid prerequisite schema.');
            } catch (InvalidArgumentException $exception) {
                $this->assertSame('prerequisite_schema_invalid', $exception->getMessage());
            }
        }
    }

    public function test_variant_uses_one_deterministic_empty_schema_when_omitted(): void
    {
        $first = new VariantDescriptor('default');
        $second = new VariantDescriptor('default');

        $this->assertSame('none-v1', $first->prerequisiteSchema->version);
        $this->assertSame($first->prerequisiteSchema->fingerprint(), $second->prerequisiteSchema->fingerprint());
        $this->assertSame([], $first->prerequisiteSchema->inputs);
        $this->assertSame([], $first->prerequisiteSchema->approvals);
    }

    public function test_request_moves_from_input_to_approval_to_ready_and_input_change_revokes_approval(): void
    {
        [$service] = $this->service($this->schema());
        $profile = Profile::factory()->for(User::factory())->create();
        $correlationId = (string) Str::uuid();
        $requestId = (string) Str::uuid();

        $created = $service->prepare($this->identity(), $profile->getKey(), $correlationId, $requestId, 'test.prepare');
        $this->assertSame(PrerequisiteState::AWAITING_INPUT, $created->state);
        $this->assertSame(['username', 'enabled', 'credential'], $created->missingInputKeys);

        $partial = $service->submit($requestId, 0, [
            'username' => ['source' => 'literal', 'value' => 'operator-a'],
        ], $correlationId, 'test.submit');
        $this->assertSame(PrerequisiteState::AWAITING_INPUT, $partial->state);
        $this->assertSame(1, $partial->lockVersion);

        $waiting = $service->submit($requestId, 1, [
            'enabled' => ['source' => 'literal', 'value' => true],
            'credential' => ['source' => 'secret_reference', 'value' => 'app-secret://app-a/account-a'],
            'count' => ['source' => 'literal', 'value' => 3],
            'tags' => ['source' => 'literal', 'value' => ['smoke', 'safe']],
        ], $correlationId, 'test.submit');
        $this->assertSame(PrerequisiteState::AWAITING_APPROVAL, $waiting->state);
        $this->assertSame([], $waiting->missingInputKeys);
        $this->assertSame(['deploy'], $waiting->missingApprovalScopes);

        $ready = $service->approve($requestId, 2, 'deploy', $correlationId, 'test.approve');
        $this->assertSame(PrerequisiteState::READY, $ready->state);
        $this->assertSame('local_cli', $ready->approvalFacts[0]->actorType);
        $this->assertSame('local-cli', $ready->approvalFacts[0]->actorReference);

        $stillReady = $service->submit($requestId, 3, [
            'count' => ['source' => 'literal', 'value' => 4],
        ], $correlationId, 'test.submit');
        $this->assertSame(PrerequisiteState::READY, $stillReady->state);

        $changed = $service->submit($requestId, 4, [
            'username' => ['source' => 'literal', 'value' => 'operator-b'],
        ], $correlationId, 'test.submit');
        $this->assertSame(PrerequisiteState::AWAITING_APPROVAL, $changed->state);
        $this->assertNotNull($changed->approvalFacts[0]->revokedAt);
        $this->assertSame(['deploy'], $changed->missingApprovalScopes);
    }

    public function test_secret_literals_and_invalid_values_are_rejected_before_persistence(): void
    {
        [$service] = $this->service($this->schema());
        $profile = Profile::factory()->for(User::factory())->create();
        $requestId = (string) Str::uuid();
        $correlationId = (string) Str::uuid();
        $service->prepare($this->identity(), $profile->getKey(), $correlationId, $requestId, 'test.prepare');

        $this->assertPrerequisiteCode('secret_literal_forbidden', fn () => $service->submit($requestId, 0, [
            'credential' => ['source' => 'literal', 'value' => 'inert-secret-sentinel'],
        ], $correlationId, 'test.submit'));
        $this->assertDatabaseMissing('acceptance_operation_inputs', ['key' => 'credential']);

        $this->assertPrerequisiteCode('input_invalid', fn () => $service->submit($requestId, 0, [
            'count' => ['source' => 'literal', 'value' => 11],
        ], $correlationId, 'test.submit'));
        $this->assertPrerequisiteCode('input_invalid', fn () => $service->submit($requestId, 0, [
            'tags' => ['source' => 'literal', 'value' => ['one', 'two', 'three', 'four']],
        ], $correlationId, 'test.submit'));
        $this->assertPrerequisiteCode('input_invalid', fn () => $service->submit($requestId, 0, [
            'unknown' => ['source' => 'literal', 'value' => 'value'],
        ], $correlationId, 'test.submit'));
        $this->assertPrerequisiteCode('input_invalid', fn () => $service->submit($requestId, 0, [
            'username' => ['source' => 'literal', 'value' => str_repeat('a', 4097)],
        ], $correlationId, 'test.submit'));
        $this->assertPrerequisiteCode('input_invalid', fn () => $service->submit($requestId, 0, [
            'credential' => ['source' => 'secret_reference', 'value' => 'app-secret://other-app/reference-a'],
        ], $correlationId, 'test.submit'));
        $this->assertSame(0, AcceptanceOperationInput::query()->count());
    }

    public function test_identical_retries_are_idempotent_and_stale_different_mutations_conflict(): void
    {
        [$service] = $this->service($this->schema());
        $profile = Profile::factory()->for(User::factory())->create();
        $requestId = (string) Str::uuid();
        $correlationId = (string) Str::uuid();
        $service->prepare($this->identity(), $profile->getKey(), $correlationId, $requestId, 'test.prepare');
        $payload = ['username' => ['source' => 'literal', 'value' => 'operator-a']];

        $first = $service->submit($requestId, 0, $payload, $correlationId, 'test.submit');
        $repeat = $service->submit($requestId, 0, $payload, $correlationId, 'test.submit');

        $this->assertSame(1, $first->lockVersion);
        $this->assertSame(1, $repeat->lockVersion);
        $this->assertPrerequisiteCode('conflict', fn () => $service->submit($requestId, 0, [
            'username' => ['source' => 'literal', 'value' => 'operator-b'],
        ], $correlationId, 'test.submit'));
    }

    public function test_expiry_schema_drift_and_terminal_cancellation_are_enforced(): void
    {
        $this->travelTo('2026-10-08 08:00:00');
        [$service, $provider] = $this->service($this->schema(requestTtl: 300));
        $profile = Profile::factory()->for(User::factory())->create();
        $correlationId = (string) Str::uuid();
        $expiredId = (string) Str::uuid();
        $service->prepare($this->identity(), $profile->getKey(), $correlationId, $expiredId, 'test.prepare');

        $this->travel(301)->seconds();
        $expired = $service->discover($expiredId, $correlationId, 'test.prepare');
        $this->assertSame(PrerequisiteState::EXPIRED, $expired->state);
        $this->assertPrerequisiteCode('request_expired', fn () => $service->submit($expiredId, $expired->lockVersion, [
            'username' => ['source' => 'literal', 'value' => 'operator-a'],
        ], $correlationId, 'test.submit'));

        $this->travelBack();
        $cancelledId = (string) Str::uuid();
        $created = $service->prepare($this->identity(), $profile->getKey(), $correlationId, $cancelledId, 'test.prepare');
        $cancelled = $service->cancel($cancelledId, $created->lockVersion, $correlationId, 'test.cancel');
        $repeat = $service->cancel($cancelledId, 0, $correlationId, 'test.cancel');
        $this->assertSame(PrerequisiteState::CANCELLED, $repeat->state);
        $this->assertSame($cancelled->lockVersion, $repeat->lockVersion);
        $this->assertPrerequisiteCode('invalid_transition', fn () => $service->submit($cancelledId, $repeat->lockVersion, [
            'username' => ['source' => 'literal', 'value' => 'operator-a'],
        ], $correlationId, 'test.submit'));

        $driftId = (string) Str::uuid();
        $service->prepare($this->identity(), $profile->getKey(), $correlationId, $driftId, 'test.prepare');
        $provider->schema = new PrerequisiteSchema('v2', requestTtlSeconds: 300);
        $this->assertPrerequisiteCode('schema_changed', fn () => $service->discover($driftId, $correlationId, 'test.prepare'));
    }

    public function test_approval_requires_its_declared_inputs_and_identical_reapproval_is_idempotent(): void
    {
        [$service] = $this->service($this->schema());
        $profile = Profile::factory()->for(User::factory())->create();
        $requestId = (string) Str::uuid();
        $correlationId = (string) Str::uuid();
        $service->prepare($this->identity(), $profile->getKey(), $correlationId, $requestId, 'test.prepare');

        $this->assertPrerequisiteCode(
            'input_required',
            fn () => $service->approve($requestId, 0, 'deploy', $correlationId, 'test.approve'),
        );
        $submitted = $service->submit($requestId, 0, [
            'username' => ['source' => 'literal', 'value' => 'operator-a'],
            'enabled' => ['source' => 'literal', 'value' => true],
            'credential' => ['source' => 'secret_reference', 'value' => 'app-secret://app-a/account-a'],
        ], $correlationId, 'test.submit');
        $approved = $service->approve($requestId, $submitted->lockVersion, 'deploy', $correlationId, 'test.approve');
        $repeat = $service->approve($requestId, 0, 'deploy', $correlationId, 'test.approve');

        $this->assertSame($approved->lockVersion, $repeat->lockVersion);
        $this->assertSame(1, AcceptanceOperationApproval::query()->count());
    }

    public function test_expired_approval_moves_a_ready_request_back_to_awaiting_approval(): void
    {
        $this->travelTo('2026-10-08 08:00:00');
        [$service] = $this->service($this->schema());
        $profile = Profile::factory()->for(User::factory())->create();
        $requestId = (string) Str::uuid();
        $correlationId = (string) Str::uuid();
        $service->prepare($this->identity(), $profile->getKey(), $correlationId, $requestId, 'test.prepare');
        $submitted = $service->submit($requestId, 0, [
            'username' => ['source' => 'literal', 'value' => 'operator-a'],
            'enabled' => ['source' => 'literal', 'value' => true],
            'credential' => ['source' => 'secret_reference', 'value' => 'app-secret://app-a/account-a'],
        ], $correlationId, 'test.submit');
        $ready = $service->approve($requestId, $submitted->lockVersion, 'deploy', $correlationId, 'test.approve');

        $this->travel(3601)->seconds();
        $expiredApproval = $service->discover($requestId, $correlationId, 'test.prepare');

        $this->assertSame(PrerequisiteState::READY, $ready->state);
        $this->assertSame(PrerequisiteState::AWAITING_APPROVAL, $expiredApproval->state);
        $this->assertSame(['deploy'], $expiredApproval->missingApprovalScopes);
        $this->assertSame($ready->lockVersion + 1, $expiredApproval->lockVersion);
    }

    public function test_initial_state_shortcuts_cancellation_and_expiry_cover_every_prerequisite_state(): void
    {
        $this->travelTo('2026-10-08 12:00:00');
        $profile = Profile::factory()->for(User::factory())->create();
        $correlationId = (string) Str::uuid();

        [$readyService] = $this->service(new PrerequisiteSchema('ready-v1', requestTtlSeconds: 300));
        $ready = $readyService->prepare(
            $this->identity(),
            $profile->getKey(),
            $correlationId,
            (string) Str::uuid(),
            'test.prepare',
        );
        $this->assertSame(PrerequisiteState::READY, $ready->state);

        [$approvalService] = $this->service(new PrerequisiteSchema('approval-v1', approvals: [
            new ApprovalRequirement('deploy', ttlSeconds: 300),
        ], requestTtlSeconds: 300));
        $awaitingApproval = $approvalService->prepare(
            $this->identity(),
            $profile->getKey(),
            $correlationId,
            (string) Str::uuid(),
            'test.prepare',
        );
        $this->assertSame(PrerequisiteState::AWAITING_APPROVAL, $awaitingApproval->state);

        [$inputService] = $this->service(new PrerequisiteSchema('input-v1', [
            new InputRequirement('enabled', InputType::BOOLEAN),
        ], requestTtlSeconds: 300));
        $awaitingInput = $inputService->prepare(
            $this->identity(),
            $profile->getKey(),
            $correlationId,
            (string) Str::uuid(),
            'test.prepare',
        );
        $inputReady = $inputService->submit($awaitingInput->requestId, 0, [
            'enabled' => ['source' => 'literal', 'value' => true],
        ], $correlationId, 'test.submit');
        $this->assertSame(PrerequisiteState::READY, $inputReady->state);

        $cancelledApproval = $approvalService->cancel(
            $awaitingApproval->requestId,
            $awaitingApproval->lockVersion,
            $correlationId,
            'test.cancel',
        );
        $this->assertSame(PrerequisiteState::CANCELLED, $cancelledApproval->state);
        $this->assertPrerequisiteCode('invalid_transition', fn () => $approvalService->approve(
            $cancelledApproval->requestId,
            $cancelledApproval->lockVersion,
            'deploy',
            $correlationId,
            'test.approve',
        ));

        $readyForExpiry = $readyService->prepare(
            $this->identity(),
            $profile->getKey(),
            $correlationId,
            (string) Str::uuid(),
            'test.prepare',
        );
        $approvalForExpiry = $approvalService->prepare(
            $this->identity(),
            $profile->getKey(),
            $correlationId,
            (string) Str::uuid(),
            'test.prepare',
        );
        $this->travel(300)->seconds();

        $this->assertSame(
            PrerequisiteState::EXPIRED,
            $readyService->discover($readyForExpiry->requestId, $correlationId, 'test.prepare')->state,
        );
        $this->assertSame(
            PrerequisiteState::EXPIRED,
            $approvalService->discover($approvalForExpiry->requestId, $correlationId, 'test.prepare')->state,
        );
        $this->assertPrerequisiteCode('request_expired', fn () => $readyService->cancel(
            $readyForExpiry->requestId,
            $readyForExpiry->lockVersion,
            $correlationId,
            'test.cancel',
        ));
    }

    /** @return array{PrerequisiteService, AcceptanceComponentProvider} */
    private function service(PrerequisiteSchema $schema): array
    {
        $provider = new class($schema) implements AcceptanceComponentProvider
        {
            public function __construct(public PrerequisiteSchema $schema) {}

            public function key(): string
            {
                return 'app-a';
            }

            public function catalogVersion(): string
            {
                return 'v2';
            }

            public function components(): iterable
            {
                yield new ComponentDescriptor('component-a');
            }

            public function suites(): iterable
            {
                yield new SuiteDescriptor('suite-a', 'component-a');
            }

            public function scenarios(): iterable
            {
                yield new ScenarioDescriptor(
                    'scenario-a',
                    'component-a',
                    'suite-a',
                    new ScenarioMetadata(
                        ['capability-a'],
                        ['tag-a'],
                        AutomationDisposition::AUTOMATED,
                        EvidenceMode::METADATA_ONLY,
                    ),
                );
            }

            public function variants(string $scenarioKey): iterable
            {
                if ($scenarioKey === 'scenario-a') {
                    yield new VariantDescriptor('variant-a', $this->schema);
                }
            }

            public function resolveScenario(
                string $componentKey,
                string $suiteKey,
                string $scenarioKey,
                string $variantKey,
            ): ?AcceptanceScenario {
                return null;
            }
        };
        $registry = new AcceptanceAppRegistry;
        $registry->register($provider);

        return [
            new PrerequisiteService(new AcceptanceCatalog($registry), new LocalCliOperatorContextProvider),
            $provider,
        ];
    }

    private function schema(int $requestTtl = 86400): PrerequisiteSchema
    {
        return new PrerequisiteSchema('v1', [
            new InputRequirement('username', InputType::STRING, approvalRelevant: true, minimum: 3, maximum: 64),
            new InputRequirement('enabled', InputType::BOOLEAN),
            new InputRequirement('credential', InputType::SECRET_REFERENCE, InputSensitivity::SECRET_REFERENCE, approvalRelevant: true),
            new InputRequirement('count', InputType::INTEGER, required: false, minimum: 1, maximum: 10),
            new InputRequirement('tags', InputType::STRING_LIST, required: false, listLimit: 3),
        ], [
            new ApprovalRequirement('deploy', ttlSeconds: min(3600, $requestTtl), invalidatedByInputKeys: ['username', 'credential']),
        ], $requestTtl);
    }

    /** @return array{app_key: string, component_key: string, suite_key: string, scenario_key: string, variant_key: string} */
    private function identity(): array
    {
        return [
            'app_key' => 'app-a',
            'component_key' => 'component-a',
            'suite_key' => 'suite-a',
            'scenario_key' => 'scenario-a',
            'variant_key' => 'variant-a',
        ];
    }

    private function assertPrerequisiteCode(string $code, callable $callback): void
    {
        try {
            $callback();
            $this->fail('Expected prerequisite exception.');
        } catch (PrerequisiteException $exception) {
            $this->assertSame($code, $exception->errorCode);
            $this->assertSame($code, $exception->getMessage());
        }
    }
}
