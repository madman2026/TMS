<?php

namespace Tests\Feature;

use App\Acceptance\Execution\AcceptanceExecutionService;
use App\Acceptance\Operations\Data\RunOperationData;
use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Contracts\TargetAccountResolver;
use App\Acceptance\Targets\Contracts\TargetBrowserRuntimeResolver;
use App\Acceptance\Targets\Contracts\TargetCleanup;
use App\Acceptance\Targets\Contracts\TargetFixtureManager;
use App\Acceptance\Targets\Contracts\TargetOracle;
use App\Acceptance\Targets\Contracts\TargetReadinessProbe;
use App\Acceptance\Targets\Data\CleanupResult;
use App\Acceptance\Targets\Data\ResourceProvisionResult;
use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetContext;
use App\Acceptance\Targets\Data\TargetEnvironment;
use App\Acceptance\Targets\Data\TargetExecutionOutcome;
use App\Acceptance\Targets\Data\TargetOracleResult;
use App\Acceptance\Targets\Data\TargetReadinessResult;
use App\Acceptance\Targets\Data\TargetResourceLifecycleData;
use App\Acceptance\Targets\TargetBrowserRuntimeRegistry;
use App\Acceptance\Targets\TargetResourceAdapters;
use App\Acceptance\Targets\TargetResourceCoordinator;
use App\Acceptance\Targets\TargetResourceRegistry;
use App\Models\Profile;
use App\Models\User;
use App\Services\AcceptanceRunService;
use App\TestStatusEnum;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\BrowserTargetRuntime;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\RunOptions;
use RuntimeException;
use Tests\TestCase;

class AcceptanceBrowserRuntimeHandoffTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_provisioned_context_resolves_and_passes_the_exact_runtime_and_identity(): void
    {
        $profile = $this->profile();
        $identity = $this->identity();
        $scenario = Mockery::mock(AcceptanceScenario::class);
        $runtime = Mockery::mock(BrowserTargetRuntime::class);
        $adapters = new BrowserRuntimeHandoffAdapters;
        $resources = $this->resourceCoordinator($adapters);
        $prerequisites = $this->prerequisites($profile);
        $runtimes = new TargetBrowserRuntimeRegistry;
        $runtimes->register('app-a', new CallbackTargetBrowserRuntimeResolver(
            function (TargetContext $context, PrerequisiteExecutionData $actualPrerequisites) use ($adapters, $runtime, $prerequisites): BrowserTargetRuntime {
                $adapters->events[] = 'resolver';
                $adapters->resolvedContext = $context;
                $adapters->resolvedPrerequisites = $actualPrerequisites;

                if ($actualPrerequisites !== $prerequisites) {
                    throw new RuntimeException('prerequisite-instance-mismatch');
                }

                return $runtime;
            },
        ));
        $test = $profile->tests()->create([
            'name' => 'Scenario A',
            'app_key' => 'app-a',
            'component_key' => 'component-a',
            'suite_key' => 'suite-a',
            'scenario_key' => 'scenario-a',
            'variant_key' => 'variant-a',
            'status' => TestStatusEnum::FINISHED,
        ]);
        $runService = Mockery::mock(AcceptanceRunService::class);
        $runService->shouldReceive('run')->once()->withArgs(
            function (
                Profile $actualProfile,
                AcceptanceExecutionIdentity $actualIdentity,
                AcceptanceScenario $actualScenario,
                BrowserTargetRuntime $actualRuntime,
            ) use ($profile, $identity, $scenario, $runtime, $adapters): bool {
                $adapters->events[] = 'run';

                return $actualProfile->is($profile)
                    && $actualIdentity === $identity
                    && $actualScenario === $scenario
                    && $actualRuntime === $runtime;
            },
        )->andReturn($test);
        $service = new AcceptanceExecutionService($runService, $resources, $runtimes);

        $result = $service->execute(
            $profile,
            $identity,
            $scenario,
            new RunOptions,
            $prerequisites,
            'dcb1cf9d-207c-4a44-963b-000000000001',
            'dcb1cf9d-207c-4a44-963b-000000000002',
        );

        $this->assertInstanceOf(RunOperationData::class, $result);
        $this->assertSame('succeeded', $result->resources->status);
        $this->assertSame(
            ['readiness', 'account', 'fixture', 'resolver', 'run', 'oracle', 'cleanup'],
            $adapters->events,
        );
        $this->assertSame(['account', 'fixture'], array_map(
            fn (ResourceReference $reference): string => $reference->type,
            $adapters->resolvedContext?->references ?? [],
        ));
        $this->assertSame($identity->value(), implode("\0", [
            $adapters->resolvedContext?->appKey,
            $adapters->resolvedContext?->componentKey,
            $adapters->resolvedContext?->suiteKey,
            $adapters->resolvedContext?->scenarioKey,
            $adapters->resolvedContext?->variantKey,
        ]));
        $this->assertSame($prerequisites, $adapters->resolvedPrerequisites);
        $this->assertStringNotContainsString('app-secret://app-a/opaque-access-a', print_r($result, true));
        $this->assertStringNotContainsString('app-secret://app-a/opaque-access-a', print_r($test->fresh()->toArray(), true));
    }

    public function test_missing_and_throwing_resolvers_fail_before_run_and_cleanup_acquired_references(): void
    {
        $profile = $this->profile();
        $identity = $this->identity();
        $scenario = Mockery::mock(AcceptanceScenario::class);
        $runService = Mockery::mock(AcceptanceRunService::class);
        $runService->shouldNotReceive('run');
        $results = [];

        foreach (['missing', 'throwing'] as $case) {
            $adapters = new BrowserRuntimeHandoffAdapters;
            $runtimes = new TargetBrowserRuntimeRegistry;
            if ($case === 'throwing') {
                $runtimes->register('app-a', new CallbackTargetBrowserRuntimeResolver(
                    function (TargetContext $context, PrerequisiteExecutionData $prerequisites) use ($adapters): BrowserTargetRuntime {
                        $adapters->events[] = 'resolver';

                        throw new RuntimeException('runtime-access-sentinel');
                    },
                ));
            }
            $service = new AcceptanceExecutionService(
                $runService,
                $this->resourceCoordinator($adapters),
                $runtimes,
            );

            $result = $service->execute(
                $profile,
                $identity,
                $scenario,
                new RunOptions,
                $this->prerequisites($profile),
                $case === 'missing'
                    ? 'dcb1cf9d-207c-4a44-963b-000000000003'
                    : 'dcb1cf9d-207c-4a44-963b-000000000004',
                'dcb1cf9d-207c-4a44-963b-000000000005',
            );

            $this->assertInstanceOf(TargetResourceLifecycleData::class, $result);
            $this->assertSame('failed', $result->status);
            $this->assertSame('execution', $result->stage);
            $this->assertSame('resource_unavailable', $result->primaryErrorCode);
            $this->assertTrue($result->cleanup?->succeeded);
            $this->assertSame(
                $case === 'missing'
                    ? ['readiness', 'account', 'fixture', 'cleanup']
                    : ['readiness', 'account', 'fixture', 'resolver', 'cleanup'],
                $adapters->events,
            );
            $this->assertCount(2, $result->cleanup?->cleanedReferences ?? []);
            $results[] = $result;
        }

        $this->assertStringNotContainsString('runtime-access-sentinel', print_r($results, true));
        $this->assertStringNotContainsString('app-secret://app-a/opaque-access-a', print_r($results, true));
        $this->assertDatabaseCount('tests', 0);
    }

    private function resourceCoordinator(BrowserRuntimeHandoffAdapters $adapter): TargetResourceCoordinator
    {
        $registry = new TargetResourceRegistry;
        $registry->register('app-a', new TargetResourceAdapters($adapter, $adapter, $adapter, $adapter, $adapter));

        return new TargetResourceCoordinator($registry);
    }

    private function prerequisites(Profile $profile): PrerequisiteExecutionData
    {
        return new PrerequisiteExecutionData(
            null,
            'app-a',
            'component-a',
            'suite-a',
            'scenario-a',
            'variant-a',
            (int) $profile->getKey(),
            ['scope' => 'synthetic'],
            ['credential' => 'app-secret://app-a/opaque-access-a'],
        );
    }

    private function identity(): AcceptanceExecutionIdentity
    {
        return new AcceptanceExecutionIdentity(
            'app-a',
            'component-a',
            'suite-a',
            'scenario-a',
            'variant-a',
        );
    }

    private function profile(): Profile
    {
        $user = User::factory()->create();

        return Profile::factory()->create(['user_id' => $user->getKey()]);
    }
}

final class CallbackTargetBrowserRuntimeResolver implements TargetBrowserRuntimeResolver
{
    public function __construct(private readonly Closure $resolve) {}

    public function resolve(
        TargetContext $context,
        PrerequisiteExecutionData $prerequisites,
    ): BrowserTargetRuntime {
        return ($this->resolve)($context, $prerequisites);
    }
}

final class BrowserRuntimeHandoffAdapters implements TargetAccountResolver, TargetCleanup, TargetFixtureManager, TargetOracle, TargetReadinessProbe
{
    /** @var list<string> */
    public array $events = [];

    public ?TargetContext $resolvedContext = null;

    public ?PrerequisiteExecutionData $resolvedPrerequisites = null;

    public function probe(TargetContext $context): TargetReadinessResult
    {
        $this->events[] = 'readiness';

        return TargetReadinessResult::ready(TargetEnvironment::TESTING);
    }

    public function resolve(TargetContext $context, PrerequisiteExecutionData $prerequisites): ResourceProvisionResult
    {
        $this->events[] = 'account';

        return ResourceProvisionResult::success([new ResourceReference('account', 'opaque-a')]);
    }

    public function provision(TargetContext $context, PrerequisiteExecutionData $prerequisites): ResourceProvisionResult
    {
        $this->events[] = 'fixture';

        return ResourceProvisionResult::success([new ResourceReference('fixture', 'opaque-b')]);
    }

    public function evaluate(TargetContext $context, TargetExecutionOutcome $execution): TargetOracleResult
    {
        $this->events[] = 'oracle';

        return TargetOracleResult::passed();
    }

    public function cleanup(TargetContext $context, int $timeoutMs): CleanupResult
    {
        $this->events[] = 'cleanup';

        return CleanupResult::success($context->references);
    }
}
