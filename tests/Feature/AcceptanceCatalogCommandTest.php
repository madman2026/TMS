<?php

namespace Tests\Feature;

use App\Acceptance\Operations\Data\OperationResult;
use App\Contracts\AcceptanceComponentProvider;
use App\Data\AcceptanceSelector;
use App\Data\ComponentDescriptor;
use App\Data\ScenarioDescriptor;
use App\Data\SuiteDescriptor;
use App\Data\VariantDescriptor;
use App\Exceptions\AcceptanceCatalogException;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceCatalog;
use App\Services\AcceptancePlanner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use RuntimeException;
use stdClass;
use Tests\TestCase;

class AcceptanceCatalogCommandTest extends TestCase
{
    public function test_commands_expose_only_version_two_hierarchy_selectors(): void
    {
        foreach (['acceptance:list', 'acceptance:plan'] as $name) {
            $definition = Artisan::all()[$name]->getDefinition();
            $this->assertSame([], $definition->getArguments());
            foreach (['app', 'component', 'suite', 'scenario', 'variant', 'capability', 'tag', 'disposition', 'evidence-mode'] as $option) {
                $this->assertTrue($definition->getOption($option)->isArray());
            }
            $this->assertSame('1000', $definition->getOption('limit')->getDefault());
            $this->assertFalse($definition->hasOption('browser'));
            $this->assertFalse($definition->hasOption('profile'));
        }
    }

    public function test_list_emits_version_two_complete_hierarchy_rows(): void
    {
        $provider = $this->bindProvider();

        $exit = Artisan::call('acceptance:list');
        $payload = json_decode(trim(Artisan::output()), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exit, json_encode($payload));
        $this->assertSame(2, $payload['schema_version']);
        $this->assertSame(2, $payload['plan_version']);
        $this->assertSame(['app-a' => 'v2'], $payload['catalog_versions']);
        $this->assertSame(3, $payload['counts']['matched']);
        $this->assertCount(3, $payload['items']);
        $this->assertSame([
            'app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key',
            'capabilities', 'tags', 'disposition', 'evidence_mode', 'executable',
        ], array_keys($payload['items'][0]));
        $this->assertSame('component-a', $payload['items'][0]['component_key']);
        $this->assertSame('suite-a', $payload['items'][0]['suite_key']);
        $this->assertSame('variant-a', $payload['items'][0]['variant_key']);

        $this->assertSame(0, $provider->resolveCalls);
    }

    public function test_component_and_suite_selectors_are_exact_hierarchy_filters(): void
    {
        $this->bindProvider();

        $exit = Artisan::call('acceptance:list', [
            '--component' => ['component-a'],
            '--suite' => ['suite-a'],
            '--scenario' => ['scenario-a'],
            '--variant' => ['variant-b'],
        ]);
        $payload = json_decode(trim(Artisan::output()), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exit, json_encode($payload));
        $this->assertSame(['scenario-a'], array_column($payload['items'], 'scenario_key'));
        $this->assertSame(['variant-b'], array_column($payload['items'], 'variant_key'));
    }

    public function test_plan_excludes_non_automated_rows_without_runtime_resolution(): void
    {
        $provider = $this->bindProvider();

        $exit = Artisan::call('acceptance:plan');
        $payload = json_decode(trim(Artisan::output()), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exit, json_encode($payload));
        $this->assertSame('planned', $payload['status']);
        $this->assertSame(['automated' => 2, 'blocked' => 1, 'not-implemented' => 0], $payload['counts']['by_disposition']);
        $this->assertSame(['scenario-a', 'scenario-a'], array_column($payload['items'], 'scenario_key'));

        $this->assertSame(0, $provider->resolveCalls);
    }

    public function test_invalid_selector_returns_version_two_error_projection(): void
    {
        $this->bindProvider();

        $exit = Artisan::call('acceptance:list', ['--component' => ['missing']]);

        $this->assertSame(2, $exit);
        $this->assertSame([
            'schema_version' => 2,
            'status' => 'rejected',
            'error_code' => 'acceptance_selector_not_found',
        ], json_decode(trim(Artisan::output()), true, flags: JSON_THROW_ON_ERROR));
    }

    public function test_empty_repeated_and_disjoint_exact_selectors_preserve_counts_and_identity(): void
    {
        $provider = $this->bindProvider();

        $selected = $this->invoke('acceptance:list', [
            '--scenario' => ['scenario-a', 'scenario-a'],
            '--variant' => ['variant-b', 'variant-a', 'variant-b'],
            '--tag' => ['tag-a'],
            '--evidence-mode' => ['metadata-only'],
        ]);
        $this->assertSame(2, $selected['counts']['matched']);
        $this->assertSame(['variant-a', 'variant-b'], array_column($selected['items'], 'variant_key'));
        $this->assertSame(['component-a', 'component-a'], array_column($selected['items'], 'component_key'));

        $empty = $this->invoke('acceptance:plan', [
            '--scenario' => ['scenario-a'],
            '--variant' => ['variant-a'],
            '--disposition' => ['blocked'],
        ]);
        $this->assertSame(0, $empty['counts']['matched']);
        $this->assertSame([], $empty['items']);
        $this->assertSame(0, $provider->resolveCalls);
    }

    public function test_inspection_error_matrix_has_fixed_version_two_output_exits_and_safe_logs(): void
    {
        Log::spy();
        $cases = [
            ['normal', ['--tag' => ['example-sensitive-value/invalid']], 'acceptance_selector_invalid', 2],
            ['normal', ['--app' => ['missing']], 'acceptance_app_not_found', 2],
            ['normal', ['--component' => ['missing']], 'acceptance_selector_not_found', 2],
            ['normal', ['--suite' => ['missing']], 'acceptance_selector_not_found', 2],
            ['normal', ['--scenario' => ['missing']], 'acceptance_selector_not_found', 2],
            ['normal', ['--variant' => ['missing']], 'acceptance_selector_not_found', 2],
            ['normal', ['--capability' => ['missing']], 'acceptance_selector_not_found', 2],
            ['normal', ['--tag' => ['missing']], 'acceptance_selector_not_found', 2],
            ['normal', ['--limit' => '1001'], 'acceptance_selector_invalid', 2],
            ['invalid-version', [], 'acceptance_catalog_invalid', 1],
            ['invalid-component', [], 'acceptance_hierarchy_invalid', 1],
            ['invalid-suite', [], 'acceptance_hierarchy_invalid', 1],
            ['duplicate-scenario', [], 'acceptance_hierarchy_duplicate', 1],
            ['duplicate-variant', [], 'acceptance_hierarchy_duplicate', 1],
            ['normal', ['--limit' => '1'], 'acceptance_catalog_limit_exceeded', 2],
            ['changed', [], 'acceptance_catalog_changed', 1],
            ['failure', [], 'acceptance_catalog_failed', 1],
        ];

        foreach (['acceptance:list', 'acceptance:plan'] as $command) {
            foreach ($cases as [$mode, $options, $code, $exit]) {
                $provider = $this->bindProvider();
                $provider->mode = $mode;

                $payload = $this->invoke($command, $options, $exit);

                $this->assertSame([
                    'schema_version' => 2,
                    'status' => $exit === 2 ? 'rejected' : 'failed',
                    'error_code' => $code,
                ], $payload);
                $this->assertSame(0, $provider->resolveCalls);
            }
        }

        $rejectedCodes = [
            'acceptance_selector_invalid',
            'acceptance_app_not_found',
            'acceptance_selector_not_found',
            'acceptance_catalog_limit_exceeded',
        ];
        Log::shouldHaveReceived('log')->times(count($cases) * 2)->withArgs(
            function (string $level, string $message, array $context) use ($rejectedCodes): bool {
                $this->assertSame('tms.acceptance.operation.failed', $message);
                $this->assertContains($context['operation'], ['acceptance.list', 'acceptance.plan']);
                $this->assertSame(in_array($context['error_code'], $rejectedCodes, true) ? 'warning' : 'error', $level);
                $this->assertTrue(OperationResult::isUuid($context['correlation_id']));
                $this->assertStringNotContainsString('example-sensitive-value', json_encode($context, JSON_THROW_ON_ERROR));

                return true;
            },
        );
    }

    public function test_raw_provider_failure_is_normalized_without_exception_chain_or_output_leakage(): void
    {
        $provider = $this->bindProvider();
        $provider->mode = 'failure';

        $payload = $this->invoke('acceptance:plan', [], 1);
        $this->assertSame('acceptance_catalog_failed', $payload['error_code']);

        try {
            (new AcceptancePlanner(new AcceptanceCatalog($this->app->make(AcceptanceAppRegistry::class))))
                ->plan(new AcceptanceSelector);
            $this->fail('Expected normalized provider failure.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame('acceptance_catalog_failed', $exception->errorCode);
            $this->assertNull($exception->getPrevious());
            $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
        }
        $this->assertSame(0, $provider->resolveCalls);
    }

    private function bindProvider(): AcceptanceComponentProvider
    {
        $provider = new class implements AcceptanceComponentProvider
        {
            public int $resolveCalls = 0;

            public string $mode = 'normal';

            private string $version = 'v2';

            public function key(): string
            {
                return 'app-a';
            }

            public function catalogVersion(): string
            {
                if ($this->mode === 'invalid-version') {
                    return null;
                }

                return $this->version;
            }

            public function components(): iterable
            {
                if ($this->mode === 'invalid-component') {
                    yield new stdClass;

                    return;
                }

                yield new ComponentDescriptor('component-a');
            }

            public function suites(): iterable
            {
                if ($this->mode === 'invalid-suite') {
                    yield new SuiteDescriptor('suite-a', 'missing-component');

                    return;
                }

                yield new SuiteDescriptor('suite-a', 'component-a');
            }

            public function scenarios(): iterable
            {
                if ($this->mode === 'failure') {
                    throw new RuntimeException('example-sensitive-value');
                }
                $automated = new ScenarioDescriptor('scenario-a', 'component-a', 'suite-a', $this->metadata(AutomationDisposition::AUTOMATED));
                yield $automated;
                if ($this->mode === 'duplicate-scenario') {
                    yield $automated;
                }
                yield new ScenarioDescriptor('scenario-b', 'component-a', 'suite-a', $this->metadata(AutomationDisposition::BLOCKED));
                if ($this->mode === 'changed') {
                    $this->version = 'v3';
                }
            }

            public function variants(string $scenarioKey): iterable
            {
                $variant = new VariantDescriptor('variant-a');
                yield $variant;
                if ($this->mode === 'duplicate-variant') {
                    yield $variant;
                }
                if ($scenarioKey === 'scenario-a') {
                    yield new VariantDescriptor('variant-b');
                }
            }

            public function resolveScenario(
                string $componentKey,
                string $suiteKey,
                string $scenarioKey,
                string $variantKey,
            ): ?AcceptanceScenario {
                $this->resolveCalls++;

                return null;
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
        };
        $registry = new AcceptanceAppRegistry;
        $registry->register($provider);
        $this->app->instance(AcceptanceAppRegistry::class, $registry);

        return $provider;
    }

    private function invoke(string $command, array $parameters = [], int $expectedExit = 0): array
    {
        $this->assertSame($expectedExit, Artisan::call($command, $parameters));
        $output = trim(Artisan::output());
        $this->assertSame(0, substr_count($output, "\n"));
        $this->assertStringNotContainsString('example-sensitive-value', $output);

        return json_decode($output, true, flags: JSON_THROW_ON_ERROR);
    }
}
