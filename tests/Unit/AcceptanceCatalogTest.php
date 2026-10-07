<?php

namespace Tests\Unit;

use App\Contracts\AcceptanceCatalogProvider;
use App\Data\ScenarioDescriptor;
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
use RuntimeException;
use stdClass;

class AcceptanceCatalogTest extends TestCase
{
    public function test_registration_and_metadata_discovery_never_materialize_a_catalog_provider(): void
    {
        $app = $this->provider([
            new ScenarioDescriptor('scenario-b', $this->metadata()),
            new ScenarioDescriptor('scenario-a', $this->metadata()),
        ]);
        $registry = new AcceptanceAppRegistry;
        $registry->register($app);
        $this->assertSame(0, $app->descriptorCalls);
        $this->assertSame(0, $app->variantCalls);
        $this->assertSame(0, $app->resolveCalls);

        $catalog = new AcceptanceCatalog($registry);
        $this->assertSame(['scenario-b', 'scenario-a'], array_map(
            fn (ScenarioDescriptor $descriptor): string => $descriptor->key,
            iterator_to_array($catalog->descriptors('local-app')),
        ));
        $this->assertSame('v1', $catalog->version('local-app'));
        $this->assertSame(['default'], array_map(
            fn (VariantDescriptor $variant): string => $variant->key,
            iterator_to_array($catalog->variants('local-app', 'scenario-a')),
        ));
        $this->assertSame(['scenario-b', 'scenario-a'], $registry->scenarioKeys('local-app'));
        $this->assertSame(['suite-a'], $registry->metadata('local-app', 'scenario-a')->suites);
        $this->assertNull($registry->metadata('local-app', 'missing'));
        $this->assertSame(0, $app->legacyCalls);
        $this->assertSame(0, $app->resolveCalls);
        $this->assertSame(0, $app->accountCalls);
    }

    public function test_invalid_descriptor_variant_and_metadata_inputs_are_safe(): void
    {
        foreach ([
            fn () => new ScenarioDescriptor('example-token-sentinel/invalid', $this->metadata()),
            fn () => new ScenarioDescriptor(str_repeat('a', 65), $this->metadata()),
            fn () => new ScenarioDescriptor("key\n", $this->metadata()),
            fn () => new VariantDescriptor('example-session-sentinel invalid'),
            fn () => new ScenarioDescriptor('valid', $this->metadata(tags: [str_repeat('a', 65)])),
            fn () => new ScenarioDescriptor('valid', $this->metadata(tags: array_map(fn ($i) => 'tag-'.$i, range(1, 65)))),
        ] as $create) {
            $this->assertCatalogError('acceptance_catalog_invalid', $create);
        }

        foreach (['descriptor', 'variant'] as $kind) {
            $app = $this->provider($kind === 'descriptor' ? [new stdClass] : [
                new ScenarioDescriptor('scenario-a', $this->metadata()),
            ], $kind === 'variant' ? [new stdClass] : [new VariantDescriptor('default')]);
            $registry = new AcceptanceAppRegistry;
            $registry->register($app);
            $catalog = new AcceptanceCatalog($registry);
            $this->assertCatalogError('acceptance_catalog_invalid', fn () => iterator_to_array(
                $kind === 'descriptor' ? $catalog->descriptors('local-app') : $catalog->variants('local-app', 'scenario-a'),
            ));
        }
    }

    public function test_duplicate_descriptor_and_variant_identity_is_scoped_and_rejected(): void
    {
        $descriptor = new ScenarioDescriptor('scenario-a', $this->metadata());
        foreach ([[$descriptor, $descriptor], [$descriptor]] as $index => $descriptors) {
            $app = $this->provider($descriptors, $index === 1
                ? [new VariantDescriptor('default'), new VariantDescriptor('default')]
                : [new VariantDescriptor('default')]);
            $registry = new AcceptanceAppRegistry;
            $registry->register($app);
            $catalog = new AcceptanceCatalog($registry);
            $this->assertCatalogError('acceptance_catalog_duplicate', fn () => iterator_to_array(
                $index === 0 ? $catalog->descriptors('local-app') : $catalog->variants('local-app', 'scenario-a'),
            ));
        }
    }

    public function test_typed_provider_definition_failures_are_invalid_without_exposing_the_type_error(): void
    {
        foreach (['descriptor', 'variant', 'version'] as $kind) {
            $app = $this->provider(
                $kind === 'descriptor' ? fn (): iterable => null : [new ScenarioDescriptor('scenario-a', $this->metadata())],
                $kind === 'variant' ? fn (): iterable => null : [new VariantDescriptor('default')],
            );
            $app->invalidVersion = $kind === 'version';
            $registry = new AcceptanceAppRegistry;
            $registry->register($app);
            $catalog = new AcceptanceCatalog($registry);
            $this->assertCatalogError('acceptance_catalog_invalid', fn () => match ($kind) {
                'descriptor' => iterator_to_array($catalog->descriptors('local-app')),
                'variant' => iterator_to_array($catalog->variants('local-app', 'scenario-a')),
                'version' => $catalog->version('local-app'),
            });
        }
    }

    public function test_provider_failures_discard_raw_text_and_previous_exceptions(): void
    {
        foreach (['descriptor', 'variant', 'version'] as $kind) {
            $app = $this->provider($kind === 'descriptor'
                ? fn () => throw new RuntimeException('example-credential-sentinel')
                : [new ScenarioDescriptor('scenario-a', $this->metadata())],
                $kind === 'variant' ? fn () => throw new RuntimeException('example-session-sentinel') : [new VariantDescriptor('default')],
            );
            $app->failVersion = $kind === 'version';
            $registry = new AcceptanceAppRegistry;
            $registry->register($app);
            $catalog = new AcceptanceCatalog($registry);
            $this->assertCatalogError('acceptance_catalog_failed', fn () => match ($kind) {
                'descriptor' => iterator_to_array($catalog->descriptors('local-app')),
                'variant' => iterator_to_array($catalog->variants('local-app', 'scenario-a')),
                'version' => $catalog->version('local-app'),
            });
        }

        $app = $this->provider([]);
        $app->revision = 'example-token-sentinel/invalid';
        $registry = new AcceptanceAppRegistry;
        $registry->register($app);
        $this->assertCatalogError('acceptance_catalog_invalid', fn () => (new AcceptanceCatalog($registry))->version('local-app'));
    }

    public function test_descriptor_variants_and_classification_are_immutable_and_canonical(): void
    {
        $key = 'tag-b';
        $keys = [&$key, 'tag-a'];
        $descriptor = new ScenarioDescriptor('scenario-a', $this->metadata(tags: $keys));
        $key = 'example-token-sentinel/invalid';
        $this->assertSame(['tag-a', 'tag-b'], ScenarioDescriptor::classification($descriptor->metadata)['tags']);
        $this->assertSame(['tag-b', 'tag-a'], $descriptor->metadata->tags);

        foreach ([$descriptor, new VariantDescriptor('default')] as $object) {
            try {
                $object->key = 'changed';
                $this->fail('Readonly identity must reject mutation.');
            } catch (Error) {
                $this->assertNotSame('changed', $object->key);
            }
        }
    }

    public function test_descriptor_traversal_stops_at_the_first_over_budget_observation(): void
    {
        $observed = 0;
        $app = $this->provider(function () use (&$observed): iterable {
            for ($i = 1; $i <= 10002; $i++) {
                $observed++;
                if ($observed > 10001) {
                    throw new RuntimeException('The catalog advanced beyond its budget.');
                }
                yield new ScenarioDescriptor('scenario-'.$i, $this->metadata());
            }
        });
        $registry = new AcceptanceAppRegistry;
        $registry->register($app);
        $this->assertCatalogError('acceptance_catalog_limit_exceeded', fn () => iterator_to_array(
            (new AcceptanceCatalog($registry))->descriptors('local-app'),
        ));
        $this->assertSame(10001, $observed);
        $this->assertSame(0, $app->resolveCalls);
    }

    private function assertCatalogError(string $code, callable $action): void
    {
        try {
            $action();
            $this->fail('Expected a safe catalog exception.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame($code, $exception->errorCode);
            $this->assertNull($exception->getPrevious());
            foreach (['example-credential-sentinel', 'example-session-sentinel', 'example-token-sentinel'] as $sentinel) {
                $this->assertStringNotContainsString($sentinel, $exception->getMessage());
            }
        }
    }

    private function metadata(array $tags = ['tag-a']): ScenarioMetadata
    {
        return new ScenarioMetadata(['suite-a'], ['cap-a'], $tags, AutomationDisposition::AUTOMATED, EvidenceMode::METADATA_ONLY);
    }

    private function provider(array|callable $descriptors, array|callable $variants = []): AcceptanceCatalogProvider
    {
        if ($variants === []) {
            $variants = [new VariantDescriptor('default')];
        }

        return new class($descriptors, $variants) implements AcceptanceCatalogProvider
        {
            public int $descriptorCalls = 0;

            public int $variantCalls = 0;

            public int $resolveCalls = 0;

            public int $legacyCalls = 0;

            public int $accountCalls = 0;

            public bool $failVersion = false;

            public bool $invalidVersion = false;

            public string $revision = 'v1';

            public function __construct(private mixed $descriptors, private mixed $variants) {}

            public function key(): string
            {
                return 'local-app';
            }

            public function catalogVersion(): string
            {
                if ($this->invalidVersion) {
                    return null;
                }
                if ($this->failVersion) {
                    throw new RuntimeException('example-token-sentinel');
                }

                return $this->revision;
            }

            public function descriptors(): iterable
            {
                $this->descriptorCalls++;
                yield from is_callable($this->descriptors) ? ($this->descriptors)() : $this->descriptors;
            }

            public function variants(string $scenarioKey): iterable
            {
                $this->variantCalls++;
                yield from is_callable($this->variants) ? ($this->variants)() : $this->variants;
            }

            public function resolveScenario(string $scenarioKey, string $variantKey): ?AcceptanceScenario
            {
                $this->resolveCalls++;
                throw new RuntimeException('example-credential-sentinel');
            }

            public function scenarios(): iterable
            {
                $this->legacyCalls++;
                throw new RuntimeException('Catalog inspection must not use legacy scenarios.');
            }
        };
    }
}
