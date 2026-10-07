<?php

namespace Tests\Unit;

use App\Contracts\AcceptanceCatalogProvider;
use App\Data\AcceptanceSelector;
use App\Data\ScenarioDescriptor;
use App\Data\VariantDescriptor;
use App\Exceptions\AcceptanceCatalogException;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceCatalog;
use App\Services\AcceptancePlanner;
use App\Services\AcceptanceVariantDispatcher;
use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class AcceptanceVariantDispatcherTest extends TestCase
{
    public function test_only_the_selected_factory_is_called_and_steps_are_never_executed(): void
    {
        $scenario = $this->scenario('scenario-a');
        $selected = $this->provider('app-a', ['default', 'other'], $scenario);
        $unselected = $this->provider('app-b', ['default'], $this->scenario('scenario-a'));
        [$registry, $dispatcher] = $this->dispatcher([$selected, $unselected]);
        $resolved = $dispatcher->resolve('app-a', 'scenario-a', 'other');
        $this->assertSame($scenario, $resolved);
        $this->assertSame([['scenario-a', 'other']], $selected->resolutions);
        $this->assertSame([], $unselected->resolutions);
        $this->assertSame(0, $unselected->descriptions);
        $this->assertSame(0, $scenario->stepsCalls);
        $this->assertSame(0, $selected->legacyCalls);

        $this->assertSame($scenario, $registry->scenario('app-a', 'scenario-a'));
        $this->assertSame(['scenario-a', 'default'], $selected->resolutions[1]);
        $this->assertNull($registry->scenario('app-a', 'missing'));
    }

    public function test_excluded_dispositions_unknown_identity_and_missing_default_never_call_a_factory(): void
    {
        foreach ([
            AutomationDisposition::MANUAL_ONLY,
            AutomationDisposition::BLOCKED,
            AutomationDisposition::NOT_IMPLEMENTED,
        ] as $disposition) {
            $app = $this->provider('app-a', ['default'], $this->scenario('scenario-a'), $disposition);
            [, $dispatcher] = $this->dispatcher([$app]);
            $this->assertError('acceptance_variant_not_executable', fn () => $dispatcher->resolve('app-a', 'scenario-a'));
            $this->assertSame([], $app->resolutions);
        }
        $app = $this->provider('app-a', ['other'], $this->scenario('scenario-a'));
        [$registry, $dispatcher] = $this->dispatcher([$app]);
        $this->assertError('acceptance_selector_not_found', fn () => $dispatcher->resolve('app-a', 'scenario-a'));
        $this->assertError('acceptance_selector_not_found', fn () => $dispatcher->resolve('app-a', 'missing', 'other'));
        $this->assertError('acceptance_app_not_found', fn () => $dispatcher->resolve('missing', 'scenario-a'));
        $this->assertError('acceptance_selector_invalid', fn () => $dispatcher->resolve('app-a', 'scenario-a', 'example-token-sentinel/invalid'));
        $this->assertNull($registry->scenario('app-a', 'scenario-a'));
        $this->assertSame([], $app->resolutions);
    }

    public function test_factory_null_key_and_metadata_mismatch_are_normalized_without_execution(): void
    {
        foreach ([
            null, $this->scenario('wrong-key'), $this->scenario('scenario-a', AutomationDisposition::BLOCKED),
        ] as $resolved) {
            $app = $this->provider('app-a', ['default'], $resolved);
            [, $dispatcher] = $this->dispatcher([$app]);
            $this->assertError('acceptance_catalog_invalid', fn () => $dispatcher->resolve('app-a', 'scenario-a'));
            $this->assertCount(1, $app->resolutions);
            if ($resolved !== null) {
                $this->assertSame(0, $resolved->stepsCalls);
            }
        }
    }

    public function test_factory_failure_and_version_drift_are_safe_and_leave_steps_unexecuted(): void
    {
        $scenario = $this->scenario('scenario-a');
        $app = $this->provider('app-a', ['default'], $scenario);
        $app->failure = true;
        [, $dispatcher] = $this->dispatcher([$app]);
        $this->assertError('acceptance_catalog_failed', fn () => $dispatcher->resolve('app-a', 'scenario-a'));
        $app->failure = false;
        $app->changeVersion = true;
        $this->assertError('acceptance_catalog_changed', fn () => $dispatcher->resolve('app-a', 'scenario-a'));
        $this->assertSame(0, $scenario->stepsCalls);
    }

    public function test_a_factory_return_type_violation_is_a_safe_invalid_catalog_failure(): void
    {
        $app = $this->provider('app-a', ['default'], $this->scenario('scenario-a'));
        $app->wrongType = true;
        [, $dispatcher] = $this->dispatcher([$app]);
        $this->assertError('acceptance_catalog_invalid', fn () => $dispatcher->resolve('app-a', 'scenario-a'));
        $this->assertCount(1, $app->resolutions);
    }

    public function test_legacy_default_variant_resolves_the_exact_cached_scenario_and_plan_does_not_run_steps(): void
    {
        $scenario = $this->scenario('scenario-a');
        $app = new class($scenario) implements AcceptanceApp
        {
            public int $enumerations = 0;

            public function __construct(private AcceptanceScenario $scenario) {}

            public function key(): string
            {
                return 'legacy-app';
            }

            public function scenarios(): iterable
            {
                $this->enumerations++;
                yield $this->scenario;
            }
        };
        [$registry, $dispatcher] = $this->dispatcher([$app]);
        $this->assertSame(0, $app->enumerations);
        $plan = (new AcceptancePlanner(new AcceptanceCatalog($registry)))->plan(new AcceptanceSelector);
        $this->assertSame(['default'], array_column($plan->toArray()['items'], 'variant_key'));
        $this->assertSame(['legacy-app' => 'legacy-v1'], $plan->catalogVersions);
        $this->assertSame($scenario, $dispatcher->resolve('legacy-app', 'scenario-a'));
        $this->assertSame(1, $app->enumerations);
        $this->assertSame(0, $scenario->stepsCalls);
    }

    public function test_shared_scenario_variant_keys_across_apps_are_distinct_tuples(): void
    {
        [$registry] = $this->dispatcher([
            $this->provider('app-a', ['default'], $this->scenario('scenario-a')),
            $this->provider('app-b', ['default'], $this->scenario('scenario-a')),
        ]);
        $plan = (new AcceptancePlanner(new AcceptanceCatalog($registry)))->plan(new AcceptanceSelector);
        $this->assertSame(['app-a', 'app-b'], array_column($plan->toArray()['items'], 'app_key'));
        $this->assertSame(['scenario-a', 'scenario-a'], array_column($plan->toArray()['items'], 'scenario_key'));
    }

    private function assertError(string $code, callable $action): void
    {
        try {
            $action();
            $this->fail('Expected a safe dispatch rejection/failure.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame($code, $exception->errorCode);
            $this->assertNull($exception->getPrevious());
            $this->assertStringNotContainsString('example-token-sentinel', $exception->getMessage());
        }
    }

    private function dispatcher(array $apps): array
    {
        $registry = new AcceptanceAppRegistry;
        foreach ($apps as $app) {
            $registry->register($app);
        }

        return [$registry, new AcceptanceVariantDispatcher(new AcceptancePlanner(new AcceptanceCatalog($registry)), $registry)];
    }

    private function scenario(string $key, AutomationDisposition $disposition = AutomationDisposition::AUTOMATED): AcceptanceScenario
    {
        return new class($key, $disposition) implements AcceptanceScenario
        {
            public int $stepsCalls = 0;

            public function __construct(private string $key, private AutomationDisposition $disposition) {}

            public function key(): string
            {
                return $this->key;
            }

            public function name(): string
            {
                return 'example-token-sentinel';
            }

            public function metadata(): ScenarioMetadata
            {
                return new ScenarioMetadata(['suite'], ['cap'], ['tag'], $this->disposition, EvidenceMode::METADATA_ONLY);
            }

            public function steps(TestContext $context): iterable
            {
                $this->stepsCalls++;
                throw new RuntimeException('Dispatch must not execute steps.');
            }
        };
    }

    private function provider(string $key, array $variants, ?AcceptanceScenario $resolved, AutomationDisposition $disposition = AutomationDisposition::AUTOMATED): AcceptanceCatalogProvider
    {
        return new class($key, $variants, $resolved, $disposition) implements AcceptanceCatalogProvider
        {
            public array $resolutions = [];

            public int $descriptions = 0;

            public int $legacyCalls = 0;

            public bool $failure = false;

            public bool $wrongType = false;

            public bool $changeVersion = false;

            public string $revision = 'v1';

            public function __construct(private string $key, private array $variants, private ?AcceptanceScenario $resolved, private AutomationDisposition $disposition) {}

            public function key(): string
            {
                return $this->key;
            }

            public function catalogVersion(): string
            {
                return $this->revision;
            }

            public function descriptors(): iterable
            {
                $this->descriptions++;
                yield new ScenarioDescriptor('scenario-a', new ScenarioMetadata(
                    ['suite'], ['cap'], ['tag'], $this->disposition, EvidenceMode::METADATA_ONLY,
                ));
            }

            public function variants(string $scenarioKey): iterable
            {
                foreach ($this->variants as $key) {
                    yield new VariantDescriptor($key);
                }
            }

            public function resolveScenario(string $scenarioKey, string $variantKey): ?AcceptanceScenario
            {
                $this->resolutions[] = [$scenarioKey, $variantKey];
                if ($this->wrongType) {
                    return new stdClass;
                }
                if ($this->failure) {
                    throw new RuntimeException('example-token-sentinel');
                }
                if ($this->changeVersion) {
                    $this->revision = 'v2';
                }

                return $this->resolved;
            }

            public function scenarios(): iterable
            {
                $this->legacyCalls++;
                throw new RuntimeException('The catalog-aware legacy path must not be used.');
            }
        };
    }
}
