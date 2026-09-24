<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceRunService;
use App\TestStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Mockery;
use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\RunOptions;
use RuntimeException;
use Tests\TestCase;

class AcceptanceRunCommandTest extends TestCase
{
    use RefreshDatabase;

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

        Log::shouldHaveReceived('error')->once()->with(
            'tms.acceptance.command.failed',
            Mockery::on(fn (array $context): bool => $context === [
                'error_code' => 'acceptance_command_failed',
                'exception_class' => RuntimeException::class,
            ]),
        );
    }

    /**
     * @return array{AcceptanceApp, AcceptanceScenario}
     */
    private function bindRegistry(): array
    {
        $scenario = new class implements AcceptanceScenario
        {
            public function key(): string
            {
                return 'local-scenario';
            }

            public function name(): string
            {
                return 'Local scenario';
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

    private function rejectedJson(string $errorCode): string
    {
        return json_encode([
            'status' => 'rejected',
            'error_code' => $errorCode,
        ], JSON_THROW_ON_ERROR);
    }
}
