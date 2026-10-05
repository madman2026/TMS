<?php

namespace Tests\Unit;

use App\Exceptions\AcceptanceRegistryException;
use App\Services\AcceptanceAppRegistry;
use InvalidArgumentException;
use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use PHPUnit\Framework\TestCase;

class AcceptanceAppRegistryTest extends TestCase
{
    public function test_it_registers_and_resolves_apps_and_scenarios_in_order(): void
    {
        $firstScenario = $this->scenario('first-scenario');
        $secondScenario = $this->scenario('second-scenario');
        $firstApp = $this->app('first-app', [$firstScenario, $secondScenario]);
        $secondApp = $this->app('second-app');
        $registry = new AcceptanceAppRegistry;

        $registry->register($firstApp);
        $registry->register($secondApp);

        $this->assertSame($firstApp, $registry->app('first-app'));
        $this->assertSame($firstScenario, $registry->scenario('first-app', 'first-scenario'));
        $this->assertNull($registry->app('missing-app'));
        $this->assertNull($registry->scenario('first-app', 'missing-scenario'));
        $this->assertSame(['first-app', 'second-app'], $registry->appKeys());
        $this->assertSame(['first-scenario', 'second-scenario'], $registry->scenarioKeys('first-app'));
    }

    public function test_it_rejects_a_duplicate_app_key_without_replacing_the_original(): void
    {
        $original = $this->app('local-app');
        $registry = new AcceptanceAppRegistry;
        $registry->register($original);

        try {
            $registry->register($this->app('local-app'));
            $this->fail('Expected an AcceptanceRegistryException.');
        } catch (AcceptanceRegistryException $exception) {
            $this->assertSame('acceptance_registry_duplicate', $exception->errorCode);
        }

        $this->assertSame($original, $registry->app('local-app'));
    }

    public function test_it_rejects_duplicate_scenario_keys_without_registering_the_app(): void
    {
        $registry = new AcceptanceAppRegistry;

        try {
            $registry->register($this->app('local-app', [
                $this->scenario('local-scenario'),
                $this->scenario('local-scenario'),
            ]));
            $this->fail('Expected an AcceptanceRegistryException.');
        } catch (AcceptanceRegistryException $exception) {
            $this->assertSame('acceptance_registry_duplicate', $exception->errorCode);
        }

        $this->assertNull($registry->app('local-app'));
    }

    public function test_it_rejects_invalid_keys_and_scenario_values_safely(): void
    {
        foreach ([
            $this->app('Invalid App'),
            $this->app('local-app', [$this->scenario('Invalid Scenario')]),
            $this->app('local-app', [new \stdClass]),
        ] as $invalidApp) {
            $registry = new AcceptanceAppRegistry;

            try {
                $registry->register($invalidApp);
                $this->fail('Expected an AcceptanceRegistryException.');
            } catch (AcceptanceRegistryException $exception) {
                $this->assertSame('acceptance_registry_invalid', $exception->errorCode);
                $this->assertSame(
                    'The Acceptance Registry definition is invalid.',
                    $exception->getMessage(),
                );
            }
        }
    }

    public function test_it_eagerly_retains_each_exact_metadata_object_without_recomputing_it(): void
    {
        $first = $this->scenario('first-scenario');
        $second = $this->scenario('second-scenario');
        $otherAppScenario = $this->scenario('first-scenario');
        $firstMetadata = $first->metadata();
        $secondMetadata = $second->metadata();
        $otherMetadata = $otherAppScenario->metadata();
        $registry = new AcceptanceAppRegistry;

        $registry->register($this->app('first-app', [$first, $second]));
        $registry->register($this->app('second-app', [$otherAppScenario]));

        $this->assertSame(2, $first->metadataCalls);
        $this->assertSame(2, $second->metadataCalls);
        $this->assertSame(2, $otherAppScenario->metadataCalls);
        $this->assertSame($firstMetadata, $registry->metadata('first-app', 'first-scenario'));
        $this->assertSame($secondMetadata, $registry->metadata('first-app', 'second-scenario'));
        $this->assertSame($otherMetadata, $registry->metadata('second-app', 'first-scenario'));
        $this->assertSame($firstMetadata, $registry->metadata('first-app', 'first-scenario'));
        $this->assertSame(['registry'], $firstMetadata->suites);
        $this->assertSame(['lookup'], $firstMetadata->capabilities);
        $this->assertSame(['unit', 'lookup-regression'], $firstMetadata->tags);
        $this->assertSame(AutomationDisposition::AUTOMATED, $firstMetadata->disposition);
        $this->assertSame(EvidenceMode::METADATA_ONLY, $firstMetadata->evidenceMode);
        $this->assertSame(2, $first->metadataCalls);
        $this->assertSame(2, $second->metadataCalls);
        $this->assertSame(2, $otherAppScenario->metadataCalls);
        $this->assertSame(0, $first->stepsCalls);
        $this->assertSame(0, $second->stepsCalls);
        $this->assertSame(0, $otherAppScenario->stepsCalls);
    }

    public function test_unknown_metadata_keys_return_null_without_mutating_registration(): void
    {
        $scenario = $this->scenario('local-scenario');
        $app = $this->app('local-app', [$scenario]);
        $registry = new AcceptanceAppRegistry;
        $registry->register($app);
        $metadata = $registry->metadata('local-app', 'local-scenario');

        $this->assertNull($registry->metadata('missing-app', 'local-scenario'));
        $this->assertNull($registry->metadata('local-app', 'missing-scenario'));
        $this->assertNull($registry->metadata('missing-app', 'missing-scenario'));
        $this->assertSame(['local-app'], $registry->appKeys());
        $this->assertSame(['local-scenario'], $registry->scenarioKeys('local-app'));
        $this->assertSame([], $registry->scenarioKeys('missing-app'));
        $this->assertSame($app, $registry->app('local-app'));
        $this->assertSame($scenario, $registry->scenario('local-app', 'local-scenario'));
        $this->assertSame($metadata, $registry->metadata('local-app', 'local-scenario'));
        $this->assertSame(1, $scenario->metadataCalls);
        $this->assertSame(0, $scenario->stepsCalls);
    }

    public function test_invalid_metadata_rejects_the_whole_app_without_partial_registration(): void
    {
        $invalid = new class implements AcceptanceScenario
        {
            public function key(): string
            {
                return 'invalid-scenario';
            }

            public function name(): string
            {
                return 'Invalid scenario';
            }

            public function metadata(): ScenarioMetadata
            {
                return new ScenarioMetadata(
                    suites: ['registry'],
                    capabilities: ['lookup'],
                    tags: ['example-sensitive-value/invalid'],
                    disposition: AutomationDisposition::AUTOMATED,
                    evidenceMode: EvidenceMode::METADATA_ONLY,
                );
            }

            public function steps(TestContext $context): iterable
            {
                throw new \LogicException('Registration must not execute a scenario.');
            }
        };
        $registry = new AcceptanceAppRegistry;
        $existing = $this->scenario('existing-scenario');
        $existingApp = $this->app('existing-app', [$existing]);
        $registry->register($existingApp);
        $existingMetadata = $registry->metadata('existing-app', 'existing-scenario');

        try {
            $registry->register($this->app('invalid-app', [$this->scenario('valid-scenario'), $invalid]));
            $this->fail('Expected invalid metadata to fail eager registration.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Scenario metadata must contain unique, valid keys.', $exception->getMessage());
            $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
        }

        $this->assertSame(['existing-app'], $registry->appKeys());
        $this->assertSame($existingApp, $registry->app('existing-app'));
        $this->assertSame($existingMetadata, $registry->metadata('existing-app', 'existing-scenario'));
        $this->assertNull($registry->app('invalid-app'));
        $this->assertSame([], $registry->scenarioKeys('invalid-app'));
        $this->assertNull($registry->scenario('invalid-app', 'valid-scenario'));
        $this->assertNull($registry->metadata('invalid-app', 'valid-scenario'));
        $this->assertNull($registry->metadata('invalid-app', 'invalid-scenario'));
    }

    /**
     * @param  array<int, mixed>  $scenarios
     */
    private function app(string $key, array $scenarios = []): AcceptanceApp
    {
        return new class($key, $scenarios) implements AcceptanceApp
        {
            /**
             * @param  array<int, mixed>  $scenarios
             */
            public function __construct(
                private readonly string $key,
                private readonly array $scenarios,
            ) {}

            public function key(): string
            {
                return $this->key;
            }

            public function scenarios(): iterable
            {
                yield from $this->scenarios;
            }
        };
    }

    private function scenario(string $key): AcceptanceScenario
    {
        $metadata = new ScenarioMetadata(
            suites: ['registry'],
            capabilities: ['lookup'],
            tags: ['unit', 'lookup-regression'],
            disposition: AutomationDisposition::AUTOMATED,
            evidenceMode: EvidenceMode::METADATA_ONLY,
        );

        return new class($key, $metadata) implements AcceptanceScenario
        {
            public int $metadataCalls = 0;

            public int $stepsCalls = 0;

            public function __construct(
                private readonly string $key,
                private readonly ScenarioMetadata $metadata,
            ) {}

            public function key(): string
            {
                return $this->key;
            }

            public function name(): string
            {
                return 'Local scenario';
            }

            public function metadata(): ScenarioMetadata
            {
                $this->metadataCalls++;

                return $this->metadata;
            }

            public function steps(TestContext $context): iterable
            {
                $this->stepsCalls++;

                return [];
            }
        };
    }
}
