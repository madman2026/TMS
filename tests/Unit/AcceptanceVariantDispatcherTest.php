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
use App\Services\AcceptancePlanner;
use App\Services\AcceptanceVariantDispatcher;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use PHPUnit\Framework\TestCase;
use stdClass;

class AcceptanceVariantDispatcherTest extends TestCase
{
    public function test_it_resolves_only_the_exact_validated_tuple(): void
    {
        [$dispatcher, $provider, $scenario] = $this->dispatcher(AutomationDisposition::AUTOMATED);
        $identity = $this->identity();

        $this->assertSame($scenario, $dispatcher->resolve($identity));
        $this->assertSame([
            ['component-a', 'suite-a', 'scenario-a', 'variant-a'],
        ], $provider->resolutions);
        $this->assertSame(0, $scenario->stepCalls);
    }

    public function test_non_executable_variant_is_rejected_before_runtime_resolution(): void
    {
        [$dispatcher, $provider] = $this->dispatcher(AutomationDisposition::BLOCKED);

        try {
            $dispatcher->resolve($this->identity());
            $this->fail('Expected blocked variant to be rejected.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame('acceptance_variant_not_executable', $exception->errorCode);
        }

        $this->assertSame([], $provider->resolutions);
    }

    public function test_null_key_and_metadata_mismatches_are_safe_invalid_catalog_failures(): void
    {
        [$dispatcher, $provider] = $this->dispatcher(AutomationDisposition::AUTOMATED);
        $provider->resolvedScenario = null;
        $this->assertDispatchError('acceptance_catalog_invalid', fn () => $dispatcher->resolve($this->identity()));

        [$dispatcher, $provider] = $this->dispatcher(AutomationDisposition::AUTOMATED);
        $provider->resolvedScenario = $this->scenario('wrong-key', $this->metadata(AutomationDisposition::AUTOMATED));
        $this->assertDispatchError('acceptance_catalog_invalid', fn () => $dispatcher->resolve($this->identity()));

        [$dispatcher, $provider] = $this->dispatcher(AutomationDisposition::AUTOMATED);
        $provider->resolvedScenario = $this->scenario('scenario-a', $this->metadata(AutomationDisposition::AUTOMATED, ['different']));
        $this->assertDispatchError('acceptance_catalog_invalid', fn () => $dispatcher->resolve($this->identity()));

        [$dispatcher, $provider] = $this->dispatcher(AutomationDisposition::AUTOMATED);
        $provider->resolvedScenario = new stdClass;
        $this->assertDispatchError('acceptance_catalog_invalid', fn () => $dispatcher->resolve($this->identity()));
    }

    public function test_resolution_failure_is_normalized_without_raw_text_or_step_execution(): void
    {
        [$dispatcher, $provider, $scenario] = $this->dispatcher(AutomationDisposition::AUTOMATED);
        $provider->failResolution = true;

        try {
            $dispatcher->resolve($this->identity());
            $this->fail('Expected safe provider failure.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame('acceptance_catalog_failed', $exception->errorCode);
            $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
            $this->assertNull($exception->getPrevious());
            $this->assertSame(0, $scenario->stepCalls);
        }
    }

    public function test_version_drift_after_planning_is_rejected(): void
    {
        [$dispatcher, $provider, $scenario] = $this->dispatcher(AutomationDisposition::AUTOMATED);
        $provider->changeVersionAfter = 3;

        $this->assertDispatchError('acceptance_catalog_changed', fn () => $dispatcher->resolve($this->identity()));
        $this->assertSame(0, $scenario->stepCalls);
    }

    public function test_missing_explicit_variant_is_rejected_without_inference(): void
    {
        [$dispatcher, $provider] = $this->dispatcher(AutomationDisposition::AUTOMATED);
        $identity = new AcceptanceExecutionIdentity('app-a', 'component-a', 'suite-a', 'scenario-a', 'missing');

        try {
            $dispatcher->resolve($identity);
            $this->fail('Expected missing variant to be rejected.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame('acceptance_selector_not_found', $exception->errorCode);
        }

        $this->assertSame([], $provider->resolutions);
    }

    public function test_shared_hierarchy_keys_across_apps_resolve_only_the_selected_provider(): void
    {
        $metadata = $this->metadata(AutomationDisposition::AUTOMATED);
        $scenarioA = $this->scenario('scenario-a', $metadata);
        $scenarioB = $this->scenario('scenario-a', $metadata);
        $providerA = $this->provider('app-a', $metadata, $scenarioA);
        $providerB = $this->provider('app-b', $metadata, $scenarioB);
        $registry = new AcceptanceAppRegistry;
        $registry->register($providerB);
        $registry->register($providerA);
        $dispatcher = new AcceptanceVariantDispatcher(
            new AcceptancePlanner(new AcceptanceCatalog($registry)),
            $registry,
        );

        $this->assertSame($scenarioA, $dispatcher->resolve($this->identity('app-a')));
        $this->assertSame($scenarioB, $dispatcher->resolve($this->identity('app-b')));
        $this->assertSame([
            ['component-a', 'suite-a', 'scenario-a', 'variant-a'],
        ], $providerA->resolutions);
        $this->assertSame([
            ['component-a', 'suite-a', 'scenario-a', 'variant-a'],
        ], $providerB->resolutions);
        $this->assertSame(0, $scenarioA->stepCalls);
        $this->assertSame(0, $scenarioB->stepCalls);
    }

    /** @return array{AcceptanceVariantDispatcher, AcceptanceComponentProvider, AcceptanceScenario} */
    private function dispatcher(AutomationDisposition $disposition): array
    {
        $metadata = $this->metadata($disposition);
        $scenario = $this->scenario('scenario-a', $metadata);
        $provider = $this->provider('app-a', $metadata, $scenario);
        $registry = new AcceptanceAppRegistry;
        $registry->register($provider);
        $dispatcher = new AcceptanceVariantDispatcher(
            new AcceptancePlanner(new AcceptanceCatalog($registry)),
            $registry,
        );

        return [$dispatcher, $provider, $scenario];
    }

    private function provider(
        string $appKey,
        ScenarioMetadata $metadata,
        AcceptanceScenario $scenario,
    ): AcceptanceComponentProvider {
        return new class($appKey, $metadata, $scenario) implements AcceptanceComponentProvider
        {
            public array $resolutions = [];

            public mixed $resolvedScenario;

            public bool $failResolution = false;

            public int $versionCalls = 0;

            public ?int $changeVersionAfter = null;

            public function __construct(
                private readonly string $appKey,
                private readonly ScenarioMetadata $scenarioMetadata,
                AcceptanceScenario $scenario,
            ) {
                $this->resolvedScenario = $scenario;
            }

            public function key(): string
            {
                return $this->appKey;
            }

            public function catalogVersion(): string
            {
                $this->versionCalls++;

                return $this->changeVersionAfter !== null && $this->versionCalls > $this->changeVersionAfter
                    ? 'changed'
                    : 'v2';
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
                yield new ScenarioDescriptor('scenario-a', 'component-a', 'suite-a', $this->scenarioMetadata);
            }

            public function variants(string $scenarioKey): iterable
            {
                yield new VariantDescriptor('variant-a');
            }

            public function resolveScenario(
                string $componentKey,
                string $suiteKey,
                string $scenarioKey,
                string $variantKey,
            ): ?AcceptanceScenario {
                $this->resolutions[] = [$componentKey, $suiteKey, $scenarioKey, $variantKey];
                if ($this->failResolution) {
                    throw new \RuntimeException('example-sensitive-value');
                }

                return $this->resolvedScenario;
            }
        };
    }

    private function identity(string $appKey = 'app-a'): AcceptanceExecutionIdentity
    {
        return new AcceptanceExecutionIdentity($appKey, 'component-a', 'suite-a', 'scenario-a', 'variant-a');
    }

    private function metadata(
        AutomationDisposition $disposition,
        array $capabilities = ['capability-a'],
    ): ScenarioMetadata {
        return new ScenarioMetadata($capabilities, ['tag-a'], $disposition, EvidenceMode::METADATA_ONLY);
    }

    private function scenario(string $key, ScenarioMetadata $metadata): AcceptanceScenario
    {
        return new class($key, $metadata) implements AcceptanceScenario
        {
            public int $stepCalls = 0;

            public function __construct(
                private readonly string $scenarioKey,
                private readonly ScenarioMetadata $scenarioMetadata,
            ) {}

            public function key(): string
            {
                return $this->scenarioKey;
            }

            public function name(): string
            {
                return 'Scenario A';
            }

            public function metadata(): ScenarioMetadata
            {
                return $this->scenarioMetadata;
            }

            public function steps(TestContext $context): iterable
            {
                $this->stepCalls++;

                return [];
            }
        };
    }

    private function assertDispatchError(string $code, callable $callback): void
    {
        try {
            $callback();
            $this->fail('Expected dispatch failure.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame($code, $exception->errorCode);
            $this->assertNull($exception->getPrevious());
        }
    }
}
