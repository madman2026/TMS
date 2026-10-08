<?php

namespace Tests\Unit;

use App\Contracts\AcceptanceComponentProvider;
use App\Data\AcceptancePlan;
use App\Data\AcceptanceSelector;
use App\Data\ComponentDescriptor;
use App\Data\ScenarioDescriptor;
use App\Data\SuiteDescriptor;
use App\Data\VariantDescriptor;
use App\Exceptions\AcceptanceCatalogException;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceCatalog;
use App\Services\AcceptancePlanner;
use Closure;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use PHPUnit\Framework\TestCase;

class AcceptancePlannerTest extends TestCase
{
    public function test_plan_uses_the_full_hierarchy_tuple_and_version_two_fingerprint(): void
    {
        $planner = $this->planner();
        $selector = new AcceptanceSelector(
            apps: ['app-a'],
            components: ['component-a'],
            suites: ['suite-a'],
            scenarios: ['scenario-a'],
            variants: ['variant-b'],
        );

        $plan = $planner->plan($selector);
        $payload = $plan->canonicalPayload();

        $this->assertSame(2, AcceptancePlan::VERSION);
        $this->assertSame(['app-a', 'component-a', 'suite-a', 'scenario-a', 'variant-b'], [
            $plan->items[0]->appKey,
            $plan->items[0]->componentKey,
            $plan->items[0]->suiteKey,
            $plan->items[0]->scenarioKey,
            $plan->items[0]->variantKey,
        ]);
        $this->assertSame(2, $payload['plan_version']);
        $this->assertSame(['component-a'], $payload['selector']['component']);
        $this->assertSame(['suite-a'], $payload['selector']['suite']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $plan->fingerprint);
    }

    public function test_list_includes_all_runtime_dispositions_and_plan_only_executable_rows(): void
    {
        $plan = $this->planner()->plan(new AcceptanceSelector);

        $listed = $plan->toArray(false);
        $planned = $plan->toArray(true);

        $this->assertSame(['automated' => 2, 'blocked' => 1, 'not-implemented' => 0], $listed['counts']['by_disposition']);
        $this->assertCount(3, $listed['items']);
        $this->assertCount(2, $planned['items']);
        $this->assertSame(['variant-a', 'variant-b'], array_column($planned['items'], 'variant_key'));
    }

    public function test_unknown_hierarchy_selector_is_rejected(): void
    {
        try {
            $this->planner()->plan(new AcceptanceSelector(components: ['missing-component']));
            $this->fail('Expected missing selector to be rejected.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame('acceptance_selector_not_found', $exception->errorCode);
        }
    }

    public function test_exact_selectors_are_or_within_dimensions_and_and_between_dimensions(): void
    {
        $selector = new AcceptanceSelector(
            components: ['component-a'],
            suites: ['suite-a'],
            scenarios: ['scenario-b', 'scenario-a'],
            variants: ['variant-b', 'blocked-variant'],
            capabilities: ['capability-a'],
            tags: ['tag-a'],
        );
        $plan = $this->planner()->plan($selector);

        $this->assertSame([
            ['scenario-a', 'variant-b'],
            ['scenario-b', 'blocked-variant'],
        ], array_map(fn ($item): array => [$item->scenarioKey, $item->variantKey], $plan->items));

        $empty = $this->planner()->plan(new AcceptanceSelector(
            scenarios: ['scenario-a'],
            dispositions: ['blocked'],
        ));
        $this->assertSame([], $empty->items);

        $knownButDisjoint = $this->planner()->plan(new AcceptanceSelector(
            scenarios: ['scenario-a'],
            variants: ['blocked-variant'],
        ));
        $this->assertSame([], $knownButDisjoint->items);
    }

    public function test_unknown_terms_are_rejected_across_every_hierarchy_and_classification_dimension(): void
    {
        foreach (['components', 'suites', 'scenarios', 'variants', 'capabilities', 'tags'] as $field) {
            try {
                $this->planner()->plan(new AcceptanceSelector(...[$field => ['missing']]));
                $this->fail('Expected unknown selector term.');
            } catch (AcceptanceCatalogException $exception) {
                $this->assertSame('acceptance_selector_not_found', $exception->errorCode);
                $this->assertNull($exception->getPrevious());
            }
        }

        try {
            $this->planner()->plan(new AcceptanceSelector(apps: ['missing']));
            $this->fail('Expected unknown app.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame('acceptance_app_not_found', $exception->errorCode);
        }
    }

    public function test_selector_validation_and_defensive_copy_are_strict(): void
    {
        foreach ([
            fn () => new AcceptanceSelector(components: ['Invalid']),
            fn () => new AcceptanceSelector(dispositions: ['unknown']),
            fn () => new AcceptanceSelector(evidenceModes: ['unknown']),
            fn () => new AcceptanceSelector(limit: 0),
            fn () => AcceptanceSelector::fromOptions(['limit' => 1000]),
            fn () => AcceptanceSelector::fromOptions(['limit' => '1001']),
            fn () => AcceptanceSelector::fromOptions(['component' => 'component-a']),
        ] as $construct) {
            try {
                $construct();
                $this->fail('Expected invalid selector.');
            } catch (AcceptanceCatalogException $exception) {
                $this->assertSame('acceptance_selector_invalid', $exception->errorCode);
                $this->assertNull($exception->getPrevious());
            }
        }

        $component = 'component-a';
        $components = [&$component, 'component-a'];
        $selector = new AcceptanceSelector(components: $components);
        $component = 'changed';
        $components[] = 'later';
        $this->assertSame(['component-a'], $selector->components);
    }

    public function test_fingerprint_is_canonical_and_tracks_approved_inputs(): void
    {
        [$planner, $provider] = $this->plannerAndProvider();
        $first = $planner->plan(new AcceptanceSelector(
            scenarios: ['scenario-b', 'scenario-a'],
            variants: ['variant-b', 'variant-a', 'blocked-variant'],
        ));
        $second = $planner->plan(new AcceptanceSelector(
            variants: ['blocked-variant', 'variant-a', 'variant-b'],
            scenarios: ['scenario-a', 'scenario-b'],
        ));

        $this->assertSame($first->fingerprint, $second->fingerprint);
        $this->assertEquals($first->canonicalPayload(), $second->canonicalPayload());

        $differentLimit = $planner->plan(new AcceptanceSelector(limit: 999));
        $this->assertNotSame($first->fingerprint, $differentLimit->fingerprint);

        $provider->version = 'v3';
        $differentVersion = $planner->plan(new AcceptanceSelector);
        $this->assertNotSame($differentLimit->fingerprint, $differentVersion->fingerprint);
    }

    public function test_filtered_scenarios_do_not_expand_unselected_variants(): void
    {
        [$planner, $provider] = $this->plannerAndProvider();

        $planner->plan(new AcceptanceSelector(scenarios: ['scenario-b']));

        $this->assertSame(['scenario-b'], $provider->variantCalls);

        [$planner, $provider] = $this->plannerAndProvider();
        $plan = $planner->plan(new AcceptanceSelector(
            scenarios: ['scenario-a'],
            variants: ['blocked-variant'],
        ));
        $this->assertSame([], $plan->items);
        $this->assertSame(['scenario-a', 'scenario-b'], $provider->variantCalls);
    }

    public function test_matched_row_limit_fails_without_returning_a_partial_plan(): void
    {
        [$planner, $provider] = $this->plannerAndProvider();

        try {
            $planner->plan(new AcceptanceSelector(limit: 1));
            $this->fail('Expected row limit failure.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame('acceptance_catalog_limit_exceeded', $exception->errorCode);
            $this->assertSame(['scenario-a'], $provider->variantCalls);
        }
    }

    public function test_version_drift_is_detected_before_fingerprinting(): void
    {
        [$planner, $provider] = $this->plannerAndProvider();
        $provider->changeVersionAfter = 1;

        try {
            $planner->plan(new AcceptanceSelector);
            $this->fail('Expected version drift failure.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame('acceptance_catalog_changed', $exception->errorCode);
            $this->assertGreaterThanOrEqual(2, $provider->versionCalls);
        }
    }

    public function test_visit_budget_is_shared_across_apps_and_counts_filtered_hierarchy_rows(): void
    {
        $observed = ['app-a' => 0, 'app-b' => 0];
        $first = $this->provider('app-a');
        $second = $this->provider('app-b');
        foreach (['app-a' => $first, 'app-b' => $second] as $key => $provider) {
            $provider->componentsFactory = function () use (&$observed, $key): iterable {
                for ($index = 0; $index < 6000; $index++) {
                    $observed[$key]++;
                    yield new ComponentDescriptor($index === 0 ? 'component-a' : 'component-'.$index);
                }
            };
        }
        $registry = new AcceptanceAppRegistry;
        $registry->register($first);
        $registry->register($second);
        $planner = new AcceptancePlanner(new AcceptanceCatalog($registry));

        try {
            $planner->plan(new AcceptanceSelector(dispositions: ['blocked']));
            $this->fail('Expected the shared hierarchy budget to stop traversal.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame('acceptance_catalog_limit_exceeded', $exception->errorCode);
            $this->assertSame(3997, $observed['app-b']);
            $this->assertSame([], $second->variantCalls);
        }
    }

    public function test_plan_at_item_limit_is_complete_repeatable_and_never_resolves_runtime_code(): void
    {
        [$planner, $provider] = $this->plannerAndProvider();
        $provider->variantsFactory = function (string $scenarioKey): iterable {
            if ($scenarioKey !== 'scenario-a') {
                yield new VariantDescriptor('blocked-variant');

                return;
            }
            for ($index = 1; $index <= 1000; $index++) {
                yield new VariantDescriptor('variant-'.$index);
            }
        };

        $first = $planner->plan(new AcceptanceSelector(scenarios: ['scenario-a']));
        $second = $planner->plan(new AcceptanceSelector(scenarios: ['scenario-a']));

        $this->assertCount(1000, $first->items);
        $this->assertSame($first->fingerprint, $second->fingerprint);
        $this->assertSame(1000, $first->toArray()['counts']['matched']);
        $this->assertSame(1000, $first->toArray()['counts']['executable']);
        $this->assertSame(0, $first->toArray()['counts']['excluded']);
        $this->assertSame(['variant-1', 'variant-10'], [
            $first->items[0]->variantKey,
            $first->items[1]->variantKey,
        ]);
    }

    public function test_later_provider_changes_to_an_earlier_app_are_detected_before_fingerprinting(): void
    {
        $first = $this->provider('app-a');
        $second = $this->provider('app-b');
        $second->scenariosFactory = function () use ($first): iterable {
            $first->version = 'v3';
            yield new ScenarioDescriptor(
                'scenario-a', 'component-a', 'suite-a', $this->metadata(AutomationDisposition::AUTOMATED),
            );
            yield new ScenarioDescriptor(
                'scenario-b', 'component-a', 'suite-a', $this->metadata(AutomationDisposition::BLOCKED),
            );
        };
        $registry = new AcceptanceAppRegistry;
        $registry->register($first);
        $registry->register($second);

        try {
            (new AcceptancePlanner(new AcceptanceCatalog($registry)))->plan(new AcceptanceSelector);
            $this->fail('Expected cross-app version drift.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame('acceptance_catalog_changed', $exception->errorCode);
        }
    }

    public function test_shared_hierarchy_keys_across_apps_remain_distinct_tuples(): void
    {
        $first = $this->provider('app-a');
        $second = $this->provider('app-b');
        $registry = new AcceptanceAppRegistry;
        $registry->register($second);
        $registry->register($first);
        $plan = (new AcceptancePlanner(new AcceptanceCatalog($registry)))->plan(new AcceptanceSelector);

        $this->assertSame([
            'app-a', 'app-a', 'app-a', 'app-b', 'app-b', 'app-b',
        ], array_column($plan->toArray(false)['items'], 'app_key'));
        $this->assertCount(6, $plan->items);
    }

    private function planner(): AcceptancePlanner
    {
        return $this->plannerAndProvider()[0];
    }

    /** @return array{AcceptancePlanner, AcceptanceComponentProvider} */
    private function plannerAndProvider(): array
    {
        $provider = $this->provider('app-a');
        $registry = new AcceptanceAppRegistry;
        $registry->register($provider);

        return [new AcceptancePlanner(new AcceptanceCatalog($registry)), $provider];
    }

    private function provider(string $appKey): AcceptanceComponentProvider
    {
        $automated = $this->metadata(AutomationDisposition::AUTOMATED);
        $blocked = $this->metadata(AutomationDisposition::BLOCKED);

        return new class($appKey, $automated, $blocked) implements AcceptanceComponentProvider
        {
            public string $version = 'v2';

            public int $versionCalls = 0;

            public ?int $changeVersionAfter = null;

            public array $variantCalls = [];

            public ?Closure $componentsFactory = null;

            public ?Closure $scenariosFactory = null;

            public ?Closure $variantsFactory = null;

            public function __construct(
                private readonly string $appKey,
                private readonly ScenarioMetadata $automated,
                private readonly ScenarioMetadata $blocked,
            ) {}

            public function key(): string
            {
                return $this->appKey;
            }

            public function catalogVersion(): string
            {
                $this->versionCalls++;

                return $this->changeVersionAfter !== null && $this->versionCalls > $this->changeVersionAfter
                    ? 'changed'
                    : $this->version;
            }

            public function components(): iterable
            {
                if ($this->componentsFactory !== null) {
                    yield from ($this->componentsFactory)();

                    return;
                }

                yield new ComponentDescriptor('component-a');
            }

            public function suites(): iterable
            {
                yield new SuiteDescriptor('suite-a', 'component-a');
            }

            public function scenarios(): iterable
            {
                if ($this->scenariosFactory !== null) {
                    yield from ($this->scenariosFactory)();

                    return;
                }

                yield new ScenarioDescriptor('scenario-a', 'component-a', 'suite-a', $this->automated);
                yield new ScenarioDescriptor('scenario-b', 'component-a', 'suite-a', $this->blocked);
            }

            public function variants(string $scenarioKey): iterable
            {
                $this->variantCalls[] = $scenarioKey;
                if ($this->variantsFactory !== null) {
                    yield from ($this->variantsFactory)($scenarioKey);

                    return;
                }

                if ($scenarioKey === 'scenario-a') {
                    yield new VariantDescriptor('variant-a');
                    yield new VariantDescriptor('variant-b');
                } else {
                    yield new VariantDescriptor('blocked-variant');
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
    }

    private function metadata(AutomationDisposition $disposition): ScenarioMetadata
    {
        return new ScenarioMetadata(
            ['capability-a'],
            ['tag-a'],
            $disposition,
            EvidenceMode::METADATA_ONLY,
        );
    }
}
