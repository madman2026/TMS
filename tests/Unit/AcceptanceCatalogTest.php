<?php

namespace Tests\Unit;

use App\Contracts\AcceptanceComponentProvider;
use App\Data\ComponentDescriptor;
use App\Data\ScenarioDescriptor;
use App\Data\SuiteDescriptor;
use App\Data\VariantDescriptor;
use App\Exceptions\AcceptanceCatalogException;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceCatalog;
use Error;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

class AcceptanceCatalogTest extends TestCase
{
    public function test_it_exposes_a_valid_complete_hierarchy_without_resolving_runtime_code(): void
    {
        $provider = $this->provider();
        $catalog = $this->catalog($provider);

        $this->assertSame(['component-a'], array_column(iterator_to_array($catalog->components('app-a')), 'key'));
        $this->assertSame(['suite-a'], array_column(iterator_to_array($catalog->suites('app-a')), 'key'));
        $this->assertSame(['scenario-a'], array_column(iterator_to_array($catalog->descriptors('app-a')), 'key'));
        $this->assertSame(['variant-a'], array_column(iterator_to_array($catalog->variants('app-a', 'scenario-a')), 'key'));
        $this->assertSame(0, $provider->resolveCalls);
    }

    public function test_it_rejects_missing_or_mismatched_hierarchy_references(): void
    {
        $provider = $this->provider(
            suites: [new SuiteDescriptor('suite-a', 'missing-component')],
        );

        $this->assertCatalogError('acceptance_hierarchy_invalid', fn () => iterator_to_array(
            $this->catalog($provider)->suites('app-a'),
        ));

        $provider = $this->provider(
            scenarios: [new ScenarioDescriptor('scenario-a', 'component-a', 'missing-suite', $this->metadata())],
        );
        $this->assertCatalogError('acceptance_hierarchy_invalid', fn () => iterator_to_array(
            $this->catalog($provider)->descriptors('app-a'),
        ));
    }

    public function test_it_rejects_duplicate_identity_in_every_hierarchy_collection(): void
    {
        $provider = $this->provider(components: [new ComponentDescriptor('component-a'), new ComponentDescriptor('component-a')]);
        $this->assertCatalogError('acceptance_hierarchy_duplicate', fn () => iterator_to_array(
            $this->catalog($provider)->components('app-a'),
        ));

        $provider = $this->provider(suites: [
            new SuiteDescriptor('suite-a', 'component-a'),
            new SuiteDescriptor('suite-a', 'component-a'),
        ]);
        $this->assertCatalogError('acceptance_hierarchy_duplicate', fn () => iterator_to_array(
            $this->catalog($provider)->suites('app-a'),
        ));

        $scenario = new ScenarioDescriptor('scenario-a', 'component-a', 'suite-a', $this->metadata());
        $provider = $this->provider(scenarios: [$scenario, $scenario]);
        $this->assertCatalogError('acceptance_hierarchy_duplicate', fn () => iterator_to_array(
            $this->catalog($provider)->descriptors('app-a'),
        ));

        $provider = $this->provider(variants: [new VariantDescriptor('variant-a'), new VariantDescriptor('variant-a')]);
        $this->assertCatalogError('acceptance_hierarchy_duplicate', fn () => iterator_to_array(
            $this->catalog($provider)->variants('app-a', 'scenario-a'),
        ));
    }

    public function test_provider_failures_are_normalized_without_sensitive_values(): void
    {
        foreach (['version', 'components', 'suites', 'scenarios', 'variants'] as $boundary) {
            $provider = $this->provider();
            $provider->failureAt = $boundary;
            $catalog = $this->catalog($provider);
            $action = match ($boundary) {
                'version' => fn () => $catalog->version('app-a'),
                'components' => fn () => iterator_to_array($catalog->components('app-a')),
                'suites' => fn () => iterator_to_array($catalog->suites('app-a')),
                'scenarios' => fn () => iterator_to_array($catalog->descriptors('app-a')),
                'variants' => fn () => iterator_to_array($catalog->variants('app-a', 'scenario-a')),
            };

            $this->assertCatalogError('acceptance_catalog_failed', $action);
        }
    }

    public function test_hierarchy_traversal_stops_at_the_fixed_budget(): void
    {
        $components = [];
        for ($index = 0; $index <= AcceptanceCatalog::MAX_VISITS; $index++) {
            $components[] = new ComponentDescriptor('component-'.$index);
        }

        $this->assertCatalogError('acceptance_catalog_limit_exceeded', fn () => iterator_to_array(
            $this->catalog($this->provider(components: $components))->components('app-a'),
        ));
    }

    public function test_invalid_collection_values_and_version_type_errors_are_normalized(): void
    {
        $this->assertCatalogError('acceptance_hierarchy_invalid', fn () => iterator_to_array(
            $this->catalog($this->provider(components: [new stdClass]))->components('app-a'),
        ));
        $this->assertCatalogError('acceptance_hierarchy_invalid', fn () => iterator_to_array(
            $this->catalog($this->provider(suites: [new stdClass]))->suites('app-a'),
        ));
        $this->assertCatalogError('acceptance_hierarchy_invalid', fn () => iterator_to_array(
            $this->catalog($this->provider(scenarios: [new stdClass]))->descriptors('app-a'),
        ));
        $this->assertCatalogError('acceptance_hierarchy_invalid', fn () => iterator_to_array(
            $this->catalog($this->provider(variants: [new stdClass]))->variants('app-a', 'scenario-a'),
        ));

        $provider = $this->provider();
        $provider->failVersionType = true;
        $this->assertCatalogError('acceptance_catalog_invalid', fn () => $this->catalog($provider)->version('app-a'));
    }

    public function test_descriptors_and_classification_are_readonly_and_canonical(): void
    {
        $metadata = new ScenarioMetadata(
            ['z-capability', 'a-capability'],
            ['z-tag', 'a-tag'],
            AutomationDisposition::AUTOMATED,
            EvidenceMode::NON_SENSITIVE_VISUAL,
        );
        $descriptor = new ScenarioDescriptor('scenario-a', 'component-a', 'suite-a', $metadata);

        $this->assertSame([
            'capabilities' => ['a-capability', 'z-capability'],
            'tags' => ['a-tag', 'z-tag'],
            'disposition' => 'automated',
            'evidence_mode' => 'non-sensitive-visual',
        ], ScenarioDescriptor::classification($descriptor->metadata));

        try {
            $descriptor->key = 'changed';
            $this->fail('Expected readonly descriptor.');
        } catch (Error) {
            $this->assertSame('scenario-a', $descriptor->key);
        }
    }

    private function catalog(AcceptanceComponentProvider $provider): AcceptanceCatalog
    {
        $registry = new AcceptanceAppRegistry;
        $registry->register($provider);

        return new AcceptanceCatalog($registry);
    }

    /**
     * @param  list<ComponentDescriptor>|null  $components
     * @param  list<SuiteDescriptor>|null  $suites
     * @param  list<ScenarioDescriptor>|null  $scenarios
     * @param  list<VariantDescriptor>|null  $variants
     */
    private function provider(
        ?array $components = null,
        ?array $suites = null,
        ?array $scenarios = null,
        ?array $variants = null,
    ): AcceptanceComponentProvider {
        return new class($components ?? [new ComponentDescriptor('component-a')], $suites ?? [new SuiteDescriptor('suite-a', 'component-a')], $scenarios ?? [new ScenarioDescriptor('scenario-a', 'component-a', 'suite-a', $this->metadata())], $variants ?? [new VariantDescriptor('variant-a')]) implements AcceptanceComponentProvider
        {
            public int $resolveCalls = 0;

            public ?string $failureAt = null;

            public bool $failVersionType = false;

            public function __construct(
                private readonly array $componentRows,
                private readonly array $suiteRows,
                private readonly array $scenarioRows,
                private readonly array $variantRows,
            ) {}

            public function key(): string
            {
                return 'app-a';
            }

            public function catalogVersion(): string
            {
                if ($this->failureAt === 'version') {
                    throw new \RuntimeException('example-sensitive-value');
                }
                if ($this->failVersionType) {
                    throw new TypeError('example-sensitive-value');
                }

                return 'v2';
            }

            public function components(): iterable
            {
                if ($this->failureAt === 'components') {
                    throw new \RuntimeException('example-sensitive-value');
                }

                yield from $this->componentRows;
            }

            public function suites(): iterable
            {
                if ($this->failureAt === 'suites') {
                    throw new \RuntimeException('example-sensitive-value');
                }

                yield from $this->suiteRows;
            }

            public function scenarios(): iterable
            {
                if ($this->failureAt === 'scenarios') {
                    throw new \RuntimeException('example-sensitive-value');
                }

                yield from $this->scenarioRows;
            }

            public function variants(string $scenarioKey): iterable
            {
                if ($this->failureAt === 'variants') {
                    throw new \RuntimeException('example-sensitive-value');
                }

                yield from $this->variantRows;
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
        };
    }

    private function metadata(): ScenarioMetadata
    {
        return new ScenarioMetadata(
            ['capability-a'],
            ['tag-a'],
            AutomationDisposition::AUTOMATED,
            EvidenceMode::METADATA_ONLY,
        );
    }

    private function assertCatalogError(string $code, callable $callback): void
    {
        try {
            $callback();
            $this->fail('Expected catalog error.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame($code, $exception->errorCode);
            $this->assertNull($exception->getPrevious());
            $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
        }
    }
}
