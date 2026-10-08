<?php

namespace Tests\Feature;

use App\Acceptance\Operations\Data\OperationResult;
use App\Contracts\AcceptanceCatalogProvider;
use App\Data\ScenarioDescriptor;
use App\Data\VariantDescriptor;
use App\Exceptions\AcceptanceRegistryException;
use App\Models\Profile;
use App\Models\User;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceRunService;
use App\TestStatusEnum;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Mockery;
use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\RunOptions;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use RuntimeException;
use Tests\TestCase;

class AcceptanceRunCommandTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        foreach (['bootstrap/cache/config.php', 'bootstrap/cache/pack-0009-config-disabled.php',
            '__tms_pack_0009_no_dotenv__', '__tms_pack_0009_no_dotenv__.testing'] as $guard) {
            if (file_exists(__DIR__.'/../../'.$guard)) {
                throw new RuntimeException('Pack 0009 isolation guard failed.');
            }
        }
        if (getenv('APP_ENV') !== 'testing' || getenv('DB_CONNECTION') !== 'sqlite'
            || getenv('DB_DATABASE') !== ':memory:') {
            throw new RuntimeException('Pack 0009 requires disposable testing configuration.');
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        if (file_exists($app->getCachedConfigPath())) {
            throw new RuntimeException('Pack 0009 isolation guard failed.');
        }
        // This runs before RefreshDatabase and retains the framework's trait initialization.
        $this->traitsUsedByTest = array_flip(class_uses_recursive(static::class));
        $app->loadEnvironmentFrom('__tms_pack_0009_no_dotenv__');
        // PHPUnit observes suppressed warnings from dotenv's absent-file probe, too.
        // Handle only that guarded missing-file warning and forward every other error.
        $previousHandler = null;
        $previousHandler = set_error_handler(static function ($severity, $message, $file, $line) use (&$previousHandler) {
            if ($severity === E_WARNING && str_contains($message, '__tms_pack_0009_no_dotenv__')
                && str_ends_with(str_replace('\\', '/', $file), '/vendor/vlucas/phpdotenv/src/Store/File/Reader.php')) {
                return true;
            }

            return $previousHandler !== null ? $previousHandler($severity, $message, $file, $line) : false;
        });
        try {
            $app->make(Kernel::class)->bootstrap();
        } finally {
            restore_error_handler();
        }
        if ($app->environment() !== 'testing' || $app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException('Pack 0009 booted an unsafe database configuration.');
        }

        return $app;
    }

    public function test_command_is_auto_discovered_with_the_expected_arguments_and_options(): void
    {
        $command = Artisan::all()['acceptance:run'];
        $definition = $command->getDefinition();

        $this->assertTrue($definition->hasArgument('app'));
        $this->assertTrue($definition->hasArgument('scenario'));
        $this->assertTrue($definition->hasArgument('profile'));
        $this->assertTrue($definition->hasOption('browser'));
        $this->assertTrue($definition->hasOption('headed'));
        $this->assertTrue($definition->hasOption('timeout'));
        $this->assertTrue($definition->hasOption('slow-mo'));
    }

    public function test_it_runs_a_registered_scenario_with_validated_options(): void
    {
        [$app, $scenario] = $this->bindRegistry();
        $profile = $this->profile();
        $test = $profile->tests()->create([
            'name' => $scenario->name(),
            'app_key' => $app->key(),
            'scenario_key' => $scenario->key(),
            'status' => TestStatusEnum::FINISHED,
            'duration' => '0.1',
        ]);
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldReceive('run')
            ->once()
            ->withArgs(function (
                Profile $actualProfile,
                AcceptanceApp $actualApp,
                AcceptanceScenario $actualScenario,
                RunOptions $options,
            ) use ($profile, $app, $scenario): bool {
                return $actualProfile->is($profile)
                    && $actualApp === $app
                    && $actualScenario === $scenario
                    && $options->browser === 'firefox'
                    && $options->headless === false
                    && $options->timeoutMs === 1234
                    && $options->slowMoMs === 25;
            })
            ->andReturn($test);
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            'app' => 'local-app',
            'scenario' => 'local-scenario',
            'profile' => $profile->getKey(),
            '--browser' => 'firefox',
            '--headed' => true,
            '--timeout' => '1234',
            '--slow-mo' => '25',
        ])->expectsOutput(json_encode([
            'status' => 'finished',
            'test_id' => $test->getKey(),
            'app_key' => 'local-app',
            'scenario_key' => 'local-scenario',
            'error_code' => null,
        ], JSON_THROW_ON_ERROR))->assertSuccessful();
    }

    public function test_an_executed_failed_run_returns_failure_with_safe_trace_fields(): void
    {
        [$app, $scenario] = $this->bindRegistry();
        $profile = $this->profile();
        $test = $profile->tests()->create([
            'name' => $scenario->name(),
            'app_key' => $app->key(),
            'scenario_key' => $scenario->key(),
            'status' => TestStatusEnum::FAILED,
            'duration' => '0.1',
            'error_code' => 'acceptance_step_failed',
        ]);
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldReceive('run')->once()->andReturn($test);
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            'app' => 'local-app',
            'scenario' => 'local-scenario',
            'profile' => $profile->getKey(),
        ])->expectsOutput(json_encode([
            'status' => 'failed',
            'test_id' => $test->getKey(),
            'app_key' => 'local-app',
            'scenario_key' => 'local-scenario',
            'error_code' => 'acceptance_step_failed',
        ], JSON_THROW_ON_ERROR))->assertExitCode(1);
    }

    public function test_omitted_options_use_existing_core_defaults(): void
    {
        config()->set([
            'core.acceptance.browser' => 'webkit',
            'core.acceptance.headless' => false,
            'core.acceptance.timeout_ms' => 2345,
            'core.acceptance.slow_mo_ms' => 15,
        ]);
        [$app, $scenario] = $this->bindRegistry();
        $profile = $this->profile();
        $test = $profile->tests()->create([
            'name' => $scenario->name(),
            'app_key' => $app->key(),
            'scenario_key' => $scenario->key(),
            'status' => TestStatusEnum::FINISHED,
        ]);
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldReceive('run')
            ->once()
            ->withArgs(fn (
                Profile $actualProfile,
                AcceptanceApp $actualApp,
                AcceptanceScenario $actualScenario,
                RunOptions $options,
            ): bool => $actualProfile->is($profile)
                && $actualApp === $app
                && $actualScenario === $scenario
                && $options->browser === 'webkit'
                && $options->headless === false
                && $options->timeoutMs === 2345
                && $options->slowMoMs === 15)
            ->andReturn($test);
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            'app' => 'local-app',
            'scenario' => 'local-scenario',
            'profile' => $profile->getKey(),
        ])->assertSuccessful();
    }

    public function test_unknown_app_is_rejected_without_echoing_input_or_calling_the_service(): void
    {
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldNotReceive('run');
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            'app' => 'example-sensitive-value',
            'scenario' => 'local-scenario',
            'profile' => 'example-sensitive-value',
        ])->expectsOutput($this->rejectedJson('acceptance_app_not_found'))
            ->doesntExpectOutputToContain('example-sensitive-value')
            ->assertExitCode(2);

        $this->assertDatabaseCount('tests', 0);
    }

    public function test_unknown_scenario_and_profile_are_rejected_before_execution(): void
    {
        $this->bindRegistry();
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldNotReceive('run');
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            'app' => 'local-app',
            'scenario' => 'missing-scenario',
            'profile' => 'example-sensitive-value',
        ])->expectsOutput($this->rejectedJson('acceptance_scenario_not_found'))
            ->doesntExpectOutputToContain('example-sensitive-value')
            ->assertExitCode(2);

        $this->artisan('acceptance:run', [
            'app' => 'local-app',
            'scenario' => 'local-scenario',
            'profile' => 'example-sensitive-value',
        ])->expectsOutput($this->rejectedJson('acceptance_profile_not_found'))
            ->doesntExpectOutputToContain('example-sensitive-value')
            ->assertExitCode(2);

        $this->assertDatabaseCount('tests', 0);
    }

    public function test_invalid_options_are_rejected_before_execution(): void
    {
        $this->bindRegistry();
        $profile = $this->profile();
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldNotReceive('run');
        $this->app->instance(AcceptanceRunService::class, $service);

        foreach ([
            ['--browser' => 'unsupported'],
            ['--timeout' => 'example-sensitive-value'],
            ['--slow-mo' => '-1'],
        ] as $invalidOptions) {
            $this->artisan('acceptance:run', [
                'app' => 'local-app',
                'scenario' => 'local-scenario',
                'profile' => $profile->getKey(),
                ...$invalidOptions,
            ])->expectsOutput($this->rejectedJson('acceptance_configuration_invalid'))
                ->doesntExpectOutputToContain('example-sensitive-value')
                ->assertExitCode(2);
        }

        $this->assertDatabaseCount('tests', 0);
    }

    public function test_unexpected_failure_is_logged_and_returned_without_raw_exception_text(): void
    {
        Log::spy();
        $this->bindRegistry();
        $profile = $this->profile();
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldReceive('run')
            ->once()
            ->andThrow(new RuntimeException('example-sensitive-value'));
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->artisan('acceptance:run', [
            'app' => 'local-app',
            'scenario' => 'local-scenario',
            'profile' => $profile->getKey(),
        ])->expectsOutput(json_encode([
            'status' => 'failed',
            'error_code' => 'acceptance_command_failed',
        ], JSON_THROW_ON_ERROR))
            ->doesntExpectOutputToContain('example-sensitive-value')
            ->assertExitCode(1);

        Log::shouldHaveReceived('log')->once()->with(
            'error', 'tms.acceptance.operation.failed',
            Mockery::on(fn (array $context): bool => $this->operationLog($context, 'acceptance_command_failed')),
        );
    }

    public function test_invalid_metadata_fails_lazy_lookup_before_profile_execution_or_persistence(): void
    {
        Log::spy();
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldNotReceive('run');
        $this->app->instance(AcceptanceRunService::class, $service);

        $this->bindRegistry(['example-sensitive-value/invalid']);
        $this->artisan('acceptance:run', [
            'app' => 'local-app',
            'scenario' => 'local-scenario',
            'profile' => 'example-sensitive-value',
        ])->expectsOutput(json_encode([
            'status' => 'failed', 'error_code' => 'acceptance_registry_invalid',
        ], JSON_THROW_ON_ERROR))->doesntExpectOutputToContain('example-sensitive-value')->assertExitCode(1);

        $this->assertDatabaseCount('tests', 0);
        $this->assertDatabaseCount('steps', 0);
        Log::shouldHaveReceived('log')->once()->with('error', 'tms.acceptance.operation.failed',
            Mockery::on(fn (array $context): bool => $this->operationLog($context, 'acceptance_registry_invalid',
                AcceptanceRegistryException::class)));
        Log::shouldNotHaveReceived('warning');
    }

    /**
     * @param  list<string>  $tags
     * @return array{AcceptanceApp, AcceptanceScenario}
     */
    private function bindRegistry(array $tags = ['command-regression']): array
    {
        $scenario = new class($tags) implements AcceptanceScenario
        {
            /**
             * @param  list<string>  $tags
             */
            public function __construct(private readonly array $tags) {}

            public function key(): string
            {
                return 'local-scenario';
            }

            public function name(): string
            {
                return 'Local scenario';
            }

            public function metadata(): ScenarioMetadata
            {
                return new ScenarioMetadata(
                    suites: ['command'],
                    capabilities: ['execution-delegation'],
                    tags: $this->tags,
                    disposition: AutomationDisposition::AUTOMATED,
                    evidenceMode: EvidenceMode::METADATA_ONLY,
                );
            }

            public function steps(TestContext $context): iterable
            {
                return [];
            }
        };
        $app = new class($scenario) implements AcceptanceApp
        {
            public function __construct(private readonly AcceptanceScenario $scenario) {}

            public function key(): string
            {
                return 'local-app';
            }

            public function scenarios(): iterable
            {
                yield $this->scenario;
            }
        };
        $registry = new AcceptanceAppRegistry;
        $registry->register($app);
        $this->app->instance(AcceptanceAppRegistry::class, $registry);

        return [$app, $scenario];
    }

    private function profile(): Profile
    {
        $user = User::factory()->create();

        return Profile::factory()->create([
            'user_id' => $user->getKey(),
            'name' => 'local-profile-'.uniqid(),
        ]);
    }

    public function test_catalog_provider_default_variant_uses_existing_single_run_output_and_options(): void
    {
        [, $scenario] = $this->bindRegistry();
        $app = new class($scenario) implements AcceptanceCatalogProvider
        {
            public array $resolutions = [];

            public function __construct(private AcceptanceScenario $scenario) {}

            public function key(): string
            {
                return 'local-app';
            }

            public function catalogVersion(): string
            {
                return 'v1';
            }

            public function descriptors(): iterable
            {
                yield new ScenarioDescriptor($this->scenario->key(), $this->scenario->metadata());
            }

            public function variants(string $scenarioKey): iterable
            {
                yield new VariantDescriptor('other');
                yield new VariantDescriptor('default');
            }

            public function resolveScenario(string $scenarioKey, string $variantKey): ?AcceptanceScenario
            {
                $this->resolutions[] = [$scenarioKey, $variantKey];

                return $this->scenario;
            }

            public function scenarios(): iterable
            {
                throw new RuntimeException('The optional provider must not use the legacy path.');
            }
        };
        $registry = new AcceptanceAppRegistry;
        $registry->register($app);
        $this->app->instance(AcceptanceAppRegistry::class, $registry);
        $profile = $this->profile();
        $test = $profile->tests()->create([
            'name' => 'Local scenario', 'app_key' => 'local-app', 'scenario_key' => 'local-scenario',
            'status' => TestStatusEnum::FINISHED,
        ]);
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldReceive('run')->once()->withArgs(fn ($actualProfile, $actualApp, $actualScenario, $options): bool => $actualProfile->is($profile) && $actualApp === $app && $actualScenario === $scenario
                && $options->browser === 'firefox' && $options->timeoutMs === 1234
        )->andReturn($test);
        $this->app->instance(AcceptanceRunService::class, $service);
        $this->artisan('acceptance:run', [
            'app' => 'local-app', 'scenario' => 'local-scenario', 'profile' => $profile->getKey(),
            '--browser' => 'firefox', '--timeout' => '1234',
        ])->expectsOutput(json_encode([
            'status' => 'finished', 'test_id' => $test->getKey(), 'app_key' => 'local-app',
            'scenario_key' => 'local-scenario', 'error_code' => null,
        ], JSON_THROW_ON_ERROR))->assertSuccessful();
        $this->assertSame([['local-scenario', 'default']], $app->resolutions);
    }

    private function rejectedJson(string $errorCode): string
    {
        return json_encode([
            'status' => 'rejected',
            'error_code' => $errorCode,
        ], JSON_THROW_ON_ERROR);
    }

    private function operationLog(array $context, string $code, ?string $exceptionClass = null): bool
    {
        $this->assertSame('acceptance.run', $context['operation']);
        $this->assertSame($code, $context['error_code']);
        $this->assertTrue(OperationResult::isUuid($context['correlation_id']));
        $this->assertTrue(OperationResult::isUuid($context['operation_id']));
        $this->assertSame($exceptionClass, $context['exception_class'] ?? null);
        $this->assertStringNotContainsString('example-sensitive-value', json_encode($context, JSON_THROW_ON_ERROR));
        $this->assertSame([], array_diff(array_keys($context), [
            'operation', 'error_code', 'correlation_id', 'operation_id', 'retryable',
            'permanent', 'admin_action_required', 'exception_class',
        ]));

        return true;
    }
}
