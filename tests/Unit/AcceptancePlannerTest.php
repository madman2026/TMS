<?php

namespace Tests\Unit;

use App\Contracts\AcceptanceCatalogProvider;
use App\Data\AcceptancePlan;
use App\Data\AcceptanceSelector;
use App\Data\ScenarioDescriptor;
use App\Data\VariantDescriptor;
use App\Exceptions\AcceptanceCatalogException;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceCatalog;
use App\Services\AcceptancePlanner;
use Error;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AcceptancePlannerTest extends TestCase
{
    public function test_selection_order_counts_and_disposition_projections_are_exact(): void
    {
        $apps = [
            $this->app('app-b', [new ScenarioDescriptor('shared', $this->metadata())], ['variant-b', 'variant-a']),
            $this->app('app-a', array_map(
                fn ($disposition) => new ScenarioDescriptor($disposition->value, $this->metadata($disposition)),
                AutomationDisposition::cases(),
            )),
        ];
        $plan = $this->planner($apps)->plan(new AcceptanceSelector);
        $listed = $plan->toArray(false);
        $planned = $plan->toArray();
        $this->assertSame([
            'matched' => 6, 'executable' => 3, 'excluded' => 3,
            'by_disposition' => ['automated' => 3, 'manual-only' => 1, 'blocked' => 1, 'not-implemented' => 1],
        ], $listed['counts']);
        $this->assertCount(6, $listed['items']);
        $this->assertCount(3, $planned['items']);
        $this->assertSame('listed', $listed['status']);
        $this->assertSame('planned', $planned['status']);
        $this->assertSame($listed['fingerprint'], $planned['fingerprint']);
        $this->assertSame([
            ['app-a', 'automated', 'default'], ['app-a', 'blocked', 'default'],
            ['app-a', 'manual-only', 'default'], ['app-a', 'not-implemented', 'default'],
            ['app-b', 'shared', 'variant-a'], ['app-b', 'shared', 'variant-b'],
        ], array_map(fn ($row) => [$row['app_key'], $row['scenario_key'], $row['variant_key']], $listed['items']));
        $this->assertSame([true, true, true], array_column($planned['items'], 'executable'));
        foreach ($apps as $app) {
            $this->assertSame(0, $app->resolutions);
            $this->assertSame(0, $app->legacyCalls);
        }
    }

    public function test_exact_selectors_or_within_and_between_dimensions_and_isolate_apps(): void
    {
        $selected = $this->app('app-a', [
            new ScenarioDescriptor('first', $this->metadata(tags: ['one'])),
            new ScenarioDescriptor('second', $this->metadata(tags: ['two'])),
            new ScenarioDescriptor('third', $this->metadata(AutomationDisposition::BLOCKED, tags: ['two'])),
        ], ['b', 'a']);
        $unselected = $this->app('app-b', fn () => throw new RuntimeException('Unselected provider was visited.'));
        $plan = $this->planner([$unselected, $selected])->plan(new AcceptanceSelector(
            apps: ['app-a'], scenarios: ['second', 'first', 'first'], variants: ['b'],
            suites: ['suite'], capabilities: ['cap'], tags: ['two', 'one'],
            dispositions: ['automated'], evidenceModes: ['metadata-only'],
        ));
        $this->assertSame(['first', 'second'], array_column($plan->toArray()['items'], 'scenario_key'));
        $this->assertSame(['b', 'b'], array_column($plan->toArray()['items'], 'variant_key'));
        $this->assertSame(0, $unselected->descriptions);
        $this->assertSame(0, $unselected->versionReads);
    }

    public function test_unknown_terms_are_validated_in_the_declared_scope_and_known_disjoint_filters_are_empty(): void
    {
        $app = $this->app('app-a', [
            new ScenarioDescriptor('first', $this->metadata(tags: ['one'])),
            new ScenarioDescriptor('second', $this->metadata(tags: ['two'])),
        ], ['default']);
        $planner = $this->planner([$app]);
        foreach (['scenarios', 'variants', 'suites', 'capabilities', 'tags'] as $field) {
            $this->assertError('acceptance_selector_not_found', fn () => $planner->plan(new AcceptanceSelector(...[
                $field => ['missing'],
            ])));
        }
        $this->assertError('acceptance_app_not_found', fn () => $planner->plan(new AcceptanceSelector(apps: ['missing'])));
        $this->assertEmpty($planner->plan(new AcceptanceSelector(scenarios: ['first'], tags: ['two']))->items);
        $this->assertEmpty($planner->plan(new AcceptanceSelector(dispositions: ['manual-only']))->items);
        $empty = $this->planner([])->plan(new AcceptanceSelector)->toArray();
        $this->assertSame(0, $empty['counts']['matched']);
        $this->assertSame([], $empty['items']);
        $this->assertSame('{}', json_encode($empty['catalog_versions']));
        $this->assertSame(64, strlen($empty['fingerprint']));

        // A requested variant remains known even when classifications exclude its Scenario.
        $this->assertEmpty($planner->plan(new AcceptanceSelector(
            scenarios: ['first'], variants: ['default'], dispositions: ['blocked'],
        ))->items);
        $this->assertError('acceptance_selector_not_found', fn () => $planner->plan(new AcceptanceSelector(
            variants: ['missing'], dispositions: ['blocked'],
        )));
    }

    public function test_filtered_scenarios_do_not_expand_variants_unless_variant_vocabulary_is_requested(): void
    {
        $app = $this->app('app-a', [new ScenarioDescriptor('first', $this->metadata())]);
        $this->planner([$app])->plan(new AcceptanceSelector(dispositions: ['blocked']));
        $this->assertSame(0, $app->variantCalls);
        $this->planner([$app])->plan(new AcceptanceSelector(variants: ['default'], dispositions: ['blocked']));
        $this->assertSame(1, $app->variantCalls);
    }

    public function test_selector_grammar_enum_and_limit_validation_and_defensive_copy(): void
    {
        foreach ([
            ['app' => ['*']], ['app' => ['a,b']], ['app' => ["app-a\n"]],
            ['tag' => ['example-token-sentinel/invalid']], ['suite' => [new \stdClass]],
            ['app' => 'app-a'], ['disposition' => ['unsupported']], ['evidence-mode' => ['unsupported']],
            ['limit' => '0'], ['limit' => '-1'], ['limit' => '1001'], ['limit' => '1.0'],
            ['limit' => str_repeat('9', 100)], ['limit' => ''], ['limit' => 1],
        ] as $options) {
            $this->assertError('acceptance_selector_invalid', fn () => AcceptanceSelector::fromOptions($options));
        }
        $appKey = 'app-b';
        $keys = [&$appKey, 'app-a', 'app-b'];
        $selector = new AcceptanceSelector(apps: $keys);
        $appKey = 'example-token-sentinel/invalid';
        $this->assertSame(['app-a', 'app-b'], $selector->apps);
        $this->assertSame(1, AcceptanceSelector::fromOptions(['limit' => '0001'])->limit);
        try {
            $selector->apps[0] = 'changed';
            $this->fail('Selector collections must be immutable.');
        } catch (Error) {
            $this->assertSame(['app-a', 'app-b'], $selector->apps);
        }
    }

    public function test_canonical_fingerprint_ignores_order_and_private_state_but_tracks_approved_inputs(): void
    {
        $a = new ScenarioDescriptor('first', $this->metadata(tags: ['two', 'one']));
        $b = new ScenarioDescriptor('second', $this->metadata(tags: ['one', 'two']));
        $app = $this->app('app-a', [$b, $a], ['b', 'a']);
        $planner = $this->planner([$app]);
        $selector = new AcceptanceSelector(tags: ['two', 'one', 'two']);
        $first = $planner->plan($selector);
        $app->definitions = [$a, $b];
        $app->variantKeys = ['a', 'b'];
        $app->privateState = 'another-inert-value';
        $second = $planner->plan(new AcceptanceSelector(tags: ['one', 'two']));
        $this->assertSame($first->fingerprint, $second->fingerprint);
        $this->assertSame(
            hash('sha256', json_encode($first->canonicalPayload(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)),
            $first->fingerprint,
        );
        $canonical = json_encode($first->canonicalPayload(), JSON_THROW_ON_ERROR);
        foreach (['example-credential-sentinel', 'example-session-sentinel', 'example-token-sentinel', 'another-inert-value'] as $sentinel) {
            $this->assertStringNotContainsString($sentinel, $canonical);
        }
        $this->assertSame(['plan_version', 'selector', 'catalog_versions', 'items'], array_keys($first->canonicalPayload()));
        $app->revision = 'v2';
        $this->assertNotSame($first->fingerprint, $planner->plan($selector)->fingerprint);
        $app->revision = 'v1';
        $app->variantKeys = ['a', 'c'];
        $this->assertNotSame($first->fingerprint, $planner->plan($selector)->fingerprint);
        $app->variantKeys = ['a', 'b'];
        $app->definitions = [new ScenarioDescriptor('first', $this->metadata(tags: ['one', 'two', 'three'])), $b];
        $this->assertNotSame($first->fingerprint, $planner->plan($selector)->fingerprint);
        $this->assertNotSame($first->fingerprint, $planner->plan(new AcceptanceSelector(tags: ['one']))->fingerprint);
        $this->assertNotSame($first->fingerprint, $planner->plan(new AcceptanceSelector(tags: ['one', 'two'], limit: 10))->fingerprint);

        $item = $first->items[0];
        $items = [&$item];
        $version = 'v1';
        $versions = ['app-a' => &$version];
        $copy = new AcceptancePlan(new AcceptanceSelector, $items, $versions);
        $item = $first->items[1];
        $version = 'v2';
        $this->assertSame($first->items[0], $copy->items[0]);
        $this->assertSame(['app-a' => 'v1'], $copy->catalogVersions);
    }

    public function test_matched_row_limit_stops_large_variant_generator_without_returning_a_partial_plan(): void
    {
        foreach ([1, 1000] as $limit) {
            $observed = 0;
            $app = $this->app('app-a', [new ScenarioDescriptor('first', $this->metadata())],
                function () use (&$observed, $limit): iterable {
                    for ($i = 1; $i <= $limit + 2; $i++) {
                        $observed++;
                        if ($observed > $limit + 1) {
                            throw new RuntimeException('Over-enumerated the provider.');
                        }
                        yield new VariantDescriptor('variant-'.$i);
                    }
                },
            );
            $this->assertError('acceptance_catalog_limit_exceeded', fn () => $this->planner([$app])->plan(new AcceptanceSelector(limit: $limit)));
            $this->assertSame($limit + 1, $observed);
            $this->assertSame(0, $app->resolutions);
        }
    }

    public function test_visit_budget_is_shared_across_apps_and_counts_filtered_descriptors(): void
    {
        $observed = ['app-a' => 0, 'app-b' => 0];
        $apps = [];
        foreach (array_keys($observed) as $key) {
            $apps[] = $this->app($key, function () use (&$observed, $key): iterable {
                for ($i = 1; $i <= 6000; $i++) {
                    $observed[$key]++;
                    yield new ScenarioDescriptor('scenario-'.$i, $this->metadata());
                }
            });
        }
        $this->assertError('acceptance_catalog_limit_exceeded', fn () => $this->planner($apps)->plan(
            new AcceptanceSelector(dispositions: ['blocked']),
        ));
        $this->assertSame(['app-a' => 6000, 'app-b' => 4001], $observed);
        $this->assertSame([0, 0], array_map(fn ($app) => $app->variantCalls, $apps));
    }

    public function test_large_plan_at_budget_is_complete_and_repeatable_without_materialization(): void
    {
        $observed = 0;
        $app = $this->app('app-a', [new ScenarioDescriptor('first', $this->metadata())],
            function () use (&$observed): iterable {
                for ($i = 1; $i <= 1000; $i++) {
                    $observed++;
                    yield new VariantDescriptor('variant-'.$i);
                }
            },
        );
        $planner = $this->planner([$app]);
        $first = $planner->plan(new AcceptanceSelector);
        $second = $planner->plan(new AcceptanceSelector);
        $this->assertCount(1000, $first->items);
        $this->assertSame($first->fingerprint, $second->fingerprint);
        $this->assertSame(1000, $first->toArray()['counts']['matched']);
        $this->assertSame(1000, $first->toArray()['counts']['executable']);
        $this->assertSame(0, $first->toArray()['counts']['excluded']);
        $this->assertSame(2000, $observed);
        $this->assertSame(0, $app->resolutions);
        $this->assertSame(0, $app->legacyCalls);
    }

    public function test_version_drift_and_duplicates_in_excluded_visited_rows_fail_closed(): void
    {
        $descriptor = new ScenarioDescriptor('first', $this->metadata());
        $app = $this->app('app-a', [$descriptor]);
        $app->definitions = function () use ($app, $descriptor): iterable {
            yield $descriptor;
            $app->revision = 'v2';
        };
        $this->assertError('acceptance_catalog_changed', fn () => $this->planner([$app])->plan(new AcceptanceSelector));

        $app->definitions = [$descriptor, $descriptor];
        $this->assertError('acceptance_catalog_duplicate', fn () => $this->planner([$app])->plan(
            new AcceptanceSelector(dispositions: ['blocked']),
        ));
        $app->definitions = [$descriptor];
        $app->variantKeys = ['default', 'default'];
        $this->assertError('acceptance_catalog_duplicate', fn () => $this->planner([$app])->plan(
            new AcceptanceSelector(variants: ['default'], dispositions: ['blocked']),
        ));
    }

    private function assertError(string $code, callable $action): void
    {
        try {
            $action();
            $this->fail('Expected a catalog failure.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame($code, $exception->errorCode);
            $this->assertNull($exception->getPrevious());
            $this->assertStringNotContainsString('example-token-sentinel', $exception->getMessage());
        }
    }

    public function test_later_provider_changes_to_an_earlier_app_are_detected_before_fingerprinting(): void
    {
        $descriptor = new ScenarioDescriptor('first', $this->metadata());
        $first = $this->app('app-a', [$descriptor]);
        $second = $this->app('app-b', function () use ($first, $descriptor): iterable {
            $first->revision = 'v2';
            yield $descriptor;
        });
        $this->assertError('acceptance_catalog_changed', fn () => $this->planner([$first, $second])->plan(new AcceptanceSelector));
        $this->assertSame(0, $first->resolutions);
        $this->assertSame(0, $second->resolutions);
    }

    private function metadata(AutomationDisposition $disposition = AutomationDisposition::AUTOMATED, array $tags = ['one']): ScenarioMetadata
    {
        return new ScenarioMetadata(['suite'], ['cap'], $tags, $disposition, EvidenceMode::METADATA_ONLY);
    }

    private function planner(array $apps): AcceptancePlanner
    {
        $registry = new AcceptanceAppRegistry;
        foreach ($apps as $app) {
            $registry->register($app);
        }

        return new AcceptancePlanner(new AcceptanceCatalog($registry));
    }

    private function app(string $key, array|callable $definitions, array|callable $variants = ['default']): AcceptanceCatalogProvider
    {
        return new class($key, $definitions, $variants) implements AcceptanceCatalogProvider
        {
            public int $descriptions = 0;

            public int $variantCalls = 0;

            public int $resolutions = 0;

            public int $legacyCalls = 0;

            public int $versionReads = 0;

            public string $revision = 'v1';

            public string $privateState = 'example-credential-sentinel|example-session-sentinel|example-token-sentinel';

            public function __construct(private string $key, public mixed $definitions, public mixed $variantKeys) {}

            public function key(): string
            {
                return $this->key;
            }

            public function catalogVersion(): string
            {
                $this->versionReads++;

                return $this->revision;
            }

            public function descriptors(): iterable
            {
                $this->descriptions++;
                yield from is_callable($this->definitions) ? ($this->definitions)() : $this->definitions;
            }

            public function variants(string $scenarioKey): iterable
            {
                $this->variantCalls++;
                if (is_callable($this->variantKeys)) {
                    yield from ($this->variantKeys)();

                    return;
                }
                foreach ($this->variantKeys as $key) {
                    yield new VariantDescriptor($key);
                }
            }

            public function resolveScenario(string $scenarioKey, string $variantKey): ?AcceptanceScenario
            {
                $this->resolutions++;
                throw new RuntimeException($this->privateState);
            }

            public function scenarios(): iterable
            {
                $this->legacyCalls++;
                throw new RuntimeException($this->privateState);
            }
        };
    }
}
