<?php

namespace Tests\Feature;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Contracts\TargetAccountResolver;
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
use App\Acceptance\Targets\TargetResourceAdapters;
use App\Acceptance\Targets\TargetResourceRegistry;
use App\Contracts\AcceptanceComponentProvider;
use App\Data\ComponentDescriptor;
use App\Data\ScenarioDescriptor;
use App\Data\SuiteDescriptor;
use App\Data\VariantDescriptor;
use App\Models\Profile;
use App\Models\User;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceRunService;
use App\TestStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Mockery;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\RunOptions;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use RuntimeException;
use Tests\TestCase;

class AcceptanceRunCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_exposes_the_complete_required_tuple_and_approved_options(): void
    {
        $definition = Artisan::all()['acceptance:run']->getDefinition();

        $this->assertSame(
            ['app', 'component', 'suite', 'scenario', 'variant', 'profile'],
            array_keys($definition->getArguments()),
        );
        foreach (['browser', 'headed', 'timeout', 'slow-mo'] as $option) {
            $this->assertTrue($definition->hasOption($option));
        }
        foreach (['app', 'component', 'suite', 'scenario', 'variant', 'profile'] as $argument) {
            $this->assertTrue($definition->getArgument($argument)->isRequired());
        }
        $this->assertFalse($definition->hasOption('default-variant'));
    }

    public function test_run_requires_and_emits_the_complete_version_two_identity(): void
    {
        [$provider, $scenario] = $this->bindProvider(AutomationDisposition::AUTOMATED);
        $profile = $this->profile();
        $test = $profile->tests()->create([
            'name' => 'Scenario A',
            ...$this->identityColumns(),
            'status' => TestStatusEnum::FINISHED,
        ]);
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldReceive('run')->once()->withArgs(function (
            Profile $actualProfile,
            AcceptanceExecutionIdentity $identity,
            AcceptanceScenario $actualScenario,
            $options,
        ) use ($profile, $scenario): bool {
            return $actualProfile->is($profile)
                && $actualScenario === $scenario
                && $identity->value() === $this->identity()->value()
                && $options->browser === 'firefox'
                && $options->timeoutMs === 1234;
        })->andReturn($test);
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            ...$this->commandIdentity(),
            'profile' => $profile->getKey(),
            '--browser' => 'firefox',
            '--timeout' => '1234',
        ])->expectsOutput(json_encode([
            'schema_version' => 2,
            'status' => 'finished',
            'test_id' => $test->getKey(),
            ...$this->identityColumns(),
            'error_code' => null,
        ], JSON_THROW_ON_ERROR))->assertSuccessful();

        $this->assertSame([
            ['component-a', 'suite-a', 'scenario-a', 'variant-a'],
        ], $provider->resolutions);
    }

    public function test_failed_execution_emits_the_complete_safe_version_two_identity(): void
    {
        [$provider] = $this->bindProvider(AutomationDisposition::AUTOMATED);
        $profile = $this->profile();
        $test = $profile->tests()->create([
            'name' => 'Scenario A',
            ...$this->identityColumns(),
            'status' => TestStatusEnum::FAILED,
            'error_code' => 'acceptance_step_failed',
        ]);
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldReceive('run')->once()->andReturn($test);
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            ...$this->commandIdentity(),
            'profile' => $profile->getKey(),
        ])->expectsOutput(json_encode([
            'schema_version' => 2,
            'status' => 'failed',
            'test_id' => $test->getKey(),
            ...$this->identityColumns(),
            'error_code' => 'acceptance_step_failed',
        ], JSON_THROW_ON_ERROR))->assertExitCode(1);

        $this->assertSame([
            ['component-a', 'suite-a', 'scenario-a', 'variant-a'],
        ], $provider->resolutions);
    }

    public function test_oracle_failure_wins_over_cleanup_failure_and_updates_the_existing_test_safely(): void
    {
        Log::spy();
        $this->bindProvider(AutomationDisposition::AUTOMATED, oraclePasses: false, cleanupPasses: false);
        $profile = $this->profile();
        $test = $profile->tests()->create([
            'name' => 'Scenario A',
            ...$this->identityColumns(),
            'status' => TestStatusEnum::FINISHED,
        ]);
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldReceive('run')->once()->andReturn($test);
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            ...$this->commandIdentity(),
            'profile' => $profile->getKey(),
        ])->expectsOutput(json_encode([
            'schema_version' => 2,
            'status' => 'failed',
            'test_id' => $test->getKey(),
            ...$this->identityColumns(),
            'error_code' => 'oracle_failed',
        ], JSON_THROW_ON_ERROR))->assertExitCode(1);

        $this->assertSame(TestStatusEnum::FAILED, $test->refresh()->status);
        $this->assertSame('oracle_failed', $test->error_code);
        Log::shouldHaveReceived('log')->once()->withArgs(function (string $level, string $message, array $context): bool {
            $encoded = json_encode($context, JSON_THROW_ON_ERROR);

            return $level === 'error'
                && $message === 'tms.acceptance.operation.failed'
                && $context['primary_error_code'] === 'oracle_failed'
                && $context['cleanup_error_code'] === 'cleanup_failed'
                && $context['resources'][0]['type'] === 'account'
                && strlen($context['resources'][0]['reference_hash']) === 64
                && ! str_contains($encoded, 'opaque-a');
        });
    }

    public function test_omitted_runtime_options_use_configured_defaults(): void
    {
        [, $scenario] = $this->bindProvider(AutomationDisposition::AUTOMATED);
        $profile = $this->profile();
        $test = $profile->tests()->create([
            'name' => 'Scenario A',
            ...$this->identityColumns(),
            'status' => TestStatusEnum::FINISHED,
        ]);
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldReceive('run')->once()->withArgs(function (
            Profile $actualProfile,
            AcceptanceExecutionIdentity $identity,
            AcceptanceScenario $actualScenario,
            RunOptions $options,
        ) use ($profile, $scenario): bool {
            $this->assertTrue($actualProfile->is($profile));
            $this->assertSame($this->identity()->value(), $identity->value());
            $this->assertSame($scenario, $actualScenario);
            $this->assertSame(config('core.acceptance.browser'), $options->browser);
            $this->assertSame(config('core.acceptance.headless'), $options->headless);
            $this->assertSame(config('core.acceptance.timeout_ms'), $options->timeoutMs);
            $this->assertSame(config('core.acceptance.slow_mo_ms'), $options->slowMoMs);

            return true;
        })->andReturn($test);
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            ...$this->commandIdentity(),
            'profile' => $profile->getKey(),
        ])->assertSuccessful();
    }

    public function test_unknown_app_and_scenario_are_rejected_before_execution_without_echoing_input(): void
    {
        [$provider] = $this->bindProvider(AutomationDisposition::AUTOMATED);
        $profile = $this->profile();
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldNotReceive('run');
        $this->app->instance(AcceptanceRunService::class, $service);

        foreach ([
            ['app', 'example-sensitive-value', 'acceptance_app_not_found'],
            ['scenario', 'example-sensitive-value', 'acceptance_selector_not_found'],
        ] as [$field, $value, $code]) {
            $identity = $this->commandIdentity();
            $identity[$field] = $value;
            $this->artisan('acceptance:run', [
                ...$identity,
                'profile' => $profile->getKey(),
            ])->expectsOutput($this->errorJson('rejected', $code))
                ->doesntExpectOutputToContain($value)
                ->assertExitCode(2);
        }

        $this->assertSame([], $provider->resolutions);
        $this->assertDatabaseCount('tests', 0);
    }

    public function test_unexpected_run_service_failure_is_normalized_logged_and_not_exposed(): void
    {
        Log::spy();
        $this->bindProvider(AutomationDisposition::AUTOMATED);
        $profile = $this->profile();
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldReceive('run')->once()->andThrow(new RuntimeException('example-sensitive-value'));
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            ...$this->commandIdentity(),
            'profile' => $profile->getKey(),
        ])->expectsOutput($this->errorJson('failed', 'acceptance_command_failed'))
            ->doesntExpectOutputToContain('example-sensitive-value')
            ->assertExitCode(1);

        Log::shouldHaveReceived('log')->once()->with(
            'error',
            'tms.acceptance.operation.failed',
            Mockery::on(function (array $context): bool {
                $this->assertSame('acceptance.run', $context['operation']);
                $this->assertSame('acceptance_command_failed', $context['error_code']);
                $this->assertSame($this->identityColumns(), array_intersect_key($context, $this->identityColumns()));
                $this->assertStringNotContainsString('example-sensitive-value', json_encode($context, JSON_THROW_ON_ERROR));

                return true;
            }),
        );
    }

    public function test_started_execution_exceptions_are_failures_with_only_approved_codes_and_context(): void
    {
        Log::spy();
        $this->bindProvider(AutomationDisposition::AUTOMATED);
        $profile = $this->profile();
        $cases = [
            'acceptance_configuration_invalid' => 'acceptance_configuration_invalid',
            'acceptance_browser_start_failed' => 'acceptance_browser_start_failed',
            'acceptance_scenario_failed' => 'acceptance_scenario_failed',
            'acceptance_result_persistence_failed' => 'acceptance_result_persistence_failed',
            'example-sensitive-code' => 'acceptance_command_failed',
        ];

        foreach ($cases as $rawCode => $expectedCode) {
            $service = Mockery::mock(AcceptanceRunService::class);
            $service->shouldReceive('run')->once()->andThrow(new AcceptanceExecutionException(
                $rawCode,
                false,
                'example-sensitive-value',
                new RuntimeException('example-sensitive-chain'),
            ));
            $this->app->instance(AcceptanceRunService::class, $service);

            $this->artisan('acceptance:run', [
                ...$this->commandIdentity(),
                'profile' => $profile->getKey(),
            ])->expectsOutput($this->errorJson('failed', $expectedCode))
                ->doesntExpectOutputToContain('example-sensitive')
                ->assertExitCode(1);
        }

        Log::shouldHaveReceived('log')->times(count($cases))->withArgs(
            function (string $level, string $message, array $context) use ($cases): bool {
                $this->assertSame('error', $level);
                $this->assertSame('tms.acceptance.operation.failed', $message);
                $this->assertContains($context['error_code'], array_values($cases));
                $this->assertSame($this->identityColumns(), array_intersect_key($context, $this->identityColumns()));
                $this->assertStringNotContainsString('example-sensitive', json_encode($context, JSON_THROW_ON_ERROR));

                return true;
            },
        );
        $this->assertDatabaseCount('tests', 0);
    }

    public function test_non_automated_tuple_is_rejected_before_profile_lookup_or_execution(): void
    {
        [$provider] = $this->bindProvider(AutomationDisposition::BLOCKED);
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldNotReceive('run');
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            ...$this->commandIdentity(),
            'profile' => '999999',
        ])->expectsOutput($this->errorJson('rejected', 'acceptance_variant_not_executable'))
            ->assertExitCode(2);

        $this->assertSame([], $provider->resolutions);
        $this->assertDatabaseCount('tests', 0);
    }

    public function test_missing_profile_is_rejected_after_exact_tuple_resolution(): void
    {
        [$provider] = $this->bindProvider(AutomationDisposition::AUTOMATED);
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldNotReceive('run');
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            ...$this->commandIdentity(),
            'profile' => '999999',
        ])->expectsOutput($this->errorJson('rejected', 'acceptance_profile_not_found'))
            ->assertExitCode(2);

        $this->assertCount(1, $provider->resolutions);
    }

    public function test_invalid_options_fail_without_starting_run_service(): void
    {
        $this->bindProvider(AutomationDisposition::AUTOMATED);
        $profile = $this->profile();
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldNotReceive('run');
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            ...$this->commandIdentity(),
            'profile' => $profile->getKey(),
            '--timeout' => '0',
        ])->expectsOutput($this->errorJson('rejected', 'acceptance_configuration_invalid'))
            ->assertExitCode(2);
    }

    public function test_each_invalid_runtime_option_is_rejected_without_persistence(): void
    {
        $this->bindProvider(AutomationDisposition::AUTOMATED);
        $profile = $this->profile();
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldNotReceive('run');
        $this->app->instance(AcceptanceRunService::class, $service);

        foreach ([
            ['--browser' => 'unsupported'],
            ['--timeout' => 'example-sensitive-value'],
            ['--slow-mo' => '-1'],
        ] as $options) {
            $this->artisan('acceptance:run', [
                ...$this->commandIdentity(),
                'profile' => $profile->getKey(),
                ...$options,
            ])->expectsOutput($this->errorJson('rejected', 'acceptance_configuration_invalid'))
                ->doesntExpectOutputToContain('example-sensitive-value')
                ->assertExitCode(2);
        }

        $this->assertDatabaseCount('tests', 0);
    }

    /** @return array{AcceptanceComponentProvider, AcceptanceScenario} */
    private function bindProvider(
        AutomationDisposition $disposition,
        bool $oraclePasses = true,
        bool $cleanupPasses = true,
    ): array {
        $metadata = new ScenarioMetadata(
            ['execution'],
            ['command'],
            $disposition,
            EvidenceMode::METADATA_ONLY,
        );
        $scenario = new class($metadata) implements AcceptanceScenario
        {
            public function __construct(private readonly ScenarioMetadata $scenarioMetadata) {}

            public function key(): string
            {
                return 'scenario-a';
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
                return [];
            }
        };
        $provider = new class($scenario, $metadata) implements AcceptanceComponentProvider
        {
            public array $resolutions = [];

            public function __construct(
                private readonly AcceptanceScenario $scenario,
                private readonly ScenarioMetadata $scenarioMetadata,
            ) {}

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

                return $this->scenario;
            }
        };
        $registry = new AcceptanceAppRegistry;
        $registry->register($provider);
        $this->app->instance(AcceptanceAppRegistry::class, $registry);
        $this->registerTargetAdapters($oraclePasses, $cleanupPasses);

        return [$provider, $scenario];
    }

    private function registerTargetAdapters(bool $oraclePasses, bool $cleanupPasses): void
    {
        $adapter = new class($oraclePasses, $cleanupPasses) implements TargetAccountResolver, TargetCleanup, TargetFixtureManager, TargetOracle, TargetReadinessProbe
        {
            public function __construct(
                private readonly bool $oraclePasses,
                private readonly bool $cleanupPasses,
            ) {}

            public function probe(TargetContext $context): TargetReadinessResult
            {
                return TargetReadinessResult::ready(TargetEnvironment::TESTING);
            }

            public function resolve(TargetContext $context, PrerequisiteExecutionData $prerequisites): ResourceProvisionResult
            {
                return ResourceProvisionResult::success([new ResourceReference('account', 'opaque-a')]);
            }

            public function provision(TargetContext $context, PrerequisiteExecutionData $prerequisites): ResourceProvisionResult
            {
                return ResourceProvisionResult::success([]);
            }

            public function evaluate(TargetContext $context, TargetExecutionOutcome $execution): TargetOracleResult
            {
                return $this->oraclePasses ? TargetOracleResult::passed() : TargetOracleResult::failed();
            }

            public function cleanup(TargetContext $context, int $timeoutMs): CleanupResult
            {
                return $this->cleanupPasses
                    ? CleanupResult::success($context->references)
                    : CleanupResult::failed([], $context->references);
            }
        };
        $registry = new TargetResourceRegistry;
        $registry->register('app-a', new TargetResourceAdapters($adapter, $adapter, $adapter, $adapter, $adapter));
        $this->app->instance(TargetResourceRegistry::class, $registry);
    }

    private function profile(): Profile
    {
        $user = User::factory()->create();

        return Profile::factory()->create([
            'user_id' => $user->getKey(),
            'name' => 'local-profile-'.uniqid(),
        ]);
    }

    private function identity(): AcceptanceExecutionIdentity
    {
        return new AcceptanceExecutionIdentity('app-a', 'component-a', 'suite-a', 'scenario-a', 'variant-a');
    }

    private function commandIdentity(): array
    {
        return [
            'app' => 'app-a',
            'component' => 'component-a',
            'suite' => 'suite-a',
            'scenario' => 'scenario-a',
            'variant' => 'variant-a',
        ];
    }

    private function identityColumns(): array
    {
        return [
            'app_key' => 'app-a',
            'component_key' => 'component-a',
            'suite_key' => 'suite-a',
            'scenario_key' => 'scenario-a',
            'variant_key' => 'variant-a',
        ];
    }

    private function errorJson(string $status, string $code): string
    {
        return json_encode([
            'schema_version' => 2,
            'status' => $status,
            'error_code' => $code,
        ], JSON_THROW_ON_ERROR);
    }
}
