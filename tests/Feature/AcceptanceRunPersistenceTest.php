<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Test as TestRun;
use App\Models\User;
use App\Services\AcceptanceRunService;
use App\TestStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\StepResult;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\RunOptions;
use Modules\Core\Data\RunResult;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Modules\Core\Services\AcceptanceRunner;
use RuntimeException;
use Tests\TestCase;

class AcceptanceRunPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_a_finished_run_outside_the_browser_transaction(): void
    {
        [$app, $scenario] = $this->appAndScenario();
        $transactionLevel = DB::transactionLevel();
        $runner = Mockery::mock(AcceptanceRunner::class);
        $runner->shouldReceive('run')
            ->once()
            ->andReturnUsing(function () use ($transactionLevel): RunResult {
                $this->assertSame($transactionLevel, DB::transactionLevel());

                return new RunResult(
                    appKey: 'local-app',
                    scenarioKey: 'local-scenario',
                    scenarioName: 'Local scenario',
                    passed: true,
                    duration: 0.25,
                    steps: [
                        new StepResult(
                            name: 'first',
                            passed: true,
                            duration: 0.1,
                            results: ['password' => 'example-sensitive-value'],
                            description: 'First local step',
                        ),
                        new StepResult(name: 'second', passed: true, duration: 0.15),
                    ],
                );
            });

        $test = (new AcceptanceRunService($runner))->run(
            $this->profile(),
            $app,
            $scenario,
            new RunOptions,
        );

        $this->assertSame(TestStatusEnum::FINISHED, $test->status);
        $this->assertSame('local-app', $test->app_key);
        $this->assertSame('local-scenario', $test->scenario_key);
        $this->assertNull($test->data);
        $this->assertCount(2, $test->steps);
        $this->assertSame(TestStatusEnum::FINISHED, $test->steps[0]->status);
        $this->assertNull($test->steps[0]->data);
        $this->assertStringNotContainsString(
            'example-sensitive-value',
            json_encode($test->load('steps')->toArray(), JSON_THROW_ON_ERROR),
        );
    }

    public function test_it_persists_and_logs_a_failed_step_without_raw_sensitive_data(): void
    {
        Log::spy();
        [$app, $scenario] = $this->appAndScenario();
        $runner = Mockery::mock(AcceptanceRunner::class);
        $runner->shouldReceive('run')->once()->andReturn(new RunResult(
            appKey: 'local-app',
            scenarioKey: 'local-scenario',
            scenarioName: 'Local scenario',
            passed: false,
            duration: 0.1,
            steps: [new StepResult(
                name: 'failed step',
                passed: false,
                error: 'example-sensitive-value',
                errorCode: 'acceptance_step_failed',
                duration: 0.1,
                critical: true,
                exceptionClass: RuntimeException::class,
            )],
            errorCode: 'acceptance_step_failed',
        ));

        $test = (new AcceptanceRunService($runner))->run($this->profile(), $app, $scenario);

        $this->assertSame(TestStatusEnum::FAILED, $test->status);
        $this->assertSame('acceptance_step_failed', $test->error_code);
        $this->assertSame(TestStatusEnum::FAILED, $test->steps[0]->status);
        $this->assertSame('Step failed.', $test->steps[0]->error_message);
        $this->assertStringNotContainsString(
            'example-sensitive-value',
            json_encode($test->load('steps')->toArray(), JSON_THROW_ON_ERROR),
        );
        Log::shouldHaveReceived('warning')->once()->with(
            'tms.acceptance.step.failed',
            Mockery::on(fn (array $context): bool => $context['test_id'] === $test->id
                && $context['error_code'] === 'acceptance_step_failed'
                && ! str_contains(json_encode($context, JSON_THROW_ON_ERROR), 'example-sensitive-value')),
        );
    }

    public function test_it_records_a_normalized_runner_failure(): void
    {
        Log::spy();
        [$app, $scenario] = $this->appAndScenario();
        $runner = Mockery::mock(AcceptanceRunner::class);
        $runner->shouldReceive('run')->once()->andThrow(
            AcceptanceExecutionException::browserStartFailed(
                new RuntimeException('example-sensitive-value'),
            ),
        );

        $test = (new AcceptanceRunService($runner))->run($this->profile(), $app, $scenario);

        $this->assertSame(TestStatusEnum::FAILED, $test->status);
        $this->assertSame('acceptance_browser_start_failed', $test->error_code);
        $this->assertStringNotContainsString(
            'example-sensitive-value',
            json_encode($test->toArray(), JSON_THROW_ON_ERROR),
        );
        Log::shouldHaveReceived('error')->once()->with(
            'tms.acceptance.run.failed',
            Mockery::on(fn (array $context): bool => $context['test_id'] === $test->id
                && $context['error_code'] === 'acceptance_browser_start_failed'
                && $context['retryable'] === true
                && ! str_contains(json_encode($context, JSON_THROW_ON_ERROR), 'example-sensitive-value')),
        );
    }

    public function test_it_normalizes_a_final_persistence_failure(): void
    {
        Log::spy();
        [$app, $scenario] = $this->appAndScenario();
        $runner = Mockery::mock(AcceptanceRunner::class);
        $runner->shouldReceive('run')->once()->andReturn(new RunResult(
            appKey: 'local-app',
            scenarioKey: 'local-scenario',
            scenarioName: 'Local scenario',
            passed: true,
            duration: 0.1,
            steps: [],
        ));
        $service = new class($runner) extends AcceptanceRunService
        {
            protected function persistResult(TestRun $test, RunResult $result): void
            {
                throw new RuntimeException('example-sensitive-value');
            }
        };

        try {
            $service->run($this->profile(), $app, $scenario);
            $this->fail('Expected an AcceptanceExecutionException.');
        } catch (AcceptanceExecutionException $exception) {
            $this->assertSame('acceptance_result_persistence_failed', $exception->errorCode);
            $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
        }

        $test = TestRun::query()->latest('id')->firstOrFail();
        $this->assertSame(TestStatusEnum::FAILED, $test->status);
        $this->assertSame('acceptance_result_persistence_failed', $test->error_code);
    }

    public function test_acceptance_metadata_migration_can_roll_back_and_reapply_in_memory(): void
    {
        $migration = require database_path(
            'migrations/2026_09_23_000001_add_acceptance_execution_fields_to_tests_and_steps_tables.php',
        );

        try {
            $migration->down();
            $this->assertFalse(Schema::hasColumn('tests', 'app_key'));
            $this->assertFalse(Schema::hasColumn('steps', 'status'));
        } finally {
            $migration->up();
        }

        $this->assertTrue(Schema::hasColumn('tests', 'app_key'));
        $this->assertTrue(Schema::hasColumn('steps', 'status'));

        $testColumns = collect(DB::select("PRAGMA table_info('tests')"))->keyBy('name');
        $stepColumns = collect(DB::select("PRAGMA table_info('steps')"))->keyBy('name');
        $testIndexes = collect(DB::select("PRAGMA index_list('tests')"))->pluck('name');
        $stepIndexes = collect(DB::select("PRAGMA index_list('steps')"))->pluck('name');

        $this->assertSame(0, $testColumns['app_key']->notnull);
        $this->assertSame(0, $testColumns['scenario_key']->notnull);
        $this->assertSame(0, $testColumns['error_code']->notnull);
        $this->assertSame(0, $stepColumns['status']->notnull);
        $this->assertSame(1, $stepColumns['critical']->notnull);
        $this->assertSame("'1'", $stepColumns['critical']->dflt_value);
        $this->assertSame(0, $stepColumns['error_code']->notnull);
        $this->assertSame(0, $stepColumns['error_message']->notnull);
        $this->assertContains('tests_app_scenario_created_index', $testIndexes);
        $this->assertContains('steps_test_status_index', $stepIndexes);
    }

    /**
     * @return array{AcceptanceApp, AcceptanceScenario}
     */
    private function appAndScenario(): array
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

        return [$app, $scenario];
    }

    private function profile(): Profile
    {
        $user = User::factory()->create();

        return Profile::factory()->create([
            'user_id' => $user->id,
            'name' => 'local-profile-'.uniqid(),
        ]);
    }
}
