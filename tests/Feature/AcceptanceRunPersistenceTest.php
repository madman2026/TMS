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
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\StepResult;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\RunOptions;
use Modules\Core\Data\RunResult;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Modules\Core\Services\AcceptanceRunner;
use RuntimeException;
use Tests\TestCase;

class AcceptanceRunPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_the_complete_identity_and_safe_finished_steps(): void
    {
        $identity = $this->identity();
        $scenario = $this->scenario();
        $runner = Mockery::mock(AcceptanceRunner::class);
        $runner->shouldReceive('run')->once()->withArgs(
            fn (AcceptanceExecutionIdentity $actual, AcceptanceScenario $actualScenario): bool => $actual->value() === $identity->value()
                && $actualScenario === $scenario,
        )->andReturn(new RunResult(
            identity: $identity,
            scenarioName: 'Scenario A',
            passed: true,
            duration: 0.25,
            steps: [new StepResult(
                name: 'first',
                passed: true,
                duration: 0.25,
                results: ['password' => 'example-sensitive-value'],
            )],
        ));

        $test = (new AcceptanceRunService($runner))->run($this->profile(), $identity, $scenario, new RunOptions);

        $this->assertSame(TestStatusEnum::FINISHED, $test->status);
        $this->assertSame([
            'app-a', 'component-a', 'suite-a', 'scenario-a', 'variant-a',
        ], [
            $test->app_key,
            $test->component_key,
            $test->suite_key,
            $test->scenario_key,
            $test->variant_key,
        ]);
        $this->assertCount(1, $test->steps);
        $this->assertNull($test->data);
        $this->assertNull($test->steps[0]->data);
        $this->assertStringNotContainsString(
            'example-sensitive-value',
            json_encode($test->load('steps')->toArray(), JSON_THROW_ON_ERROR),
        );
    }

    public function test_it_persists_and_logs_failure_with_the_complete_safe_identity(): void
    {
        Log::spy();
        $identity = $this->identity();
        $runner = Mockery::mock(AcceptanceRunner::class);
        $runner->shouldReceive('run')->once()->andThrow(
            AcceptanceExecutionException::browserStartFailed(new RuntimeException('example-sensitive-value')),
        );

        $test = (new AcceptanceRunService($runner))->run($this->profile(), $identity, $this->scenario());

        $this->assertSame(TestStatusEnum::FAILED, $test->status);
        $this->assertSame('acceptance_browser_start_failed', $test->error_code);
        Log::shouldHaveReceived('error')->once()->with(
            'tms.acceptance.run.failed',
            Mockery::on(fn (array $context): bool => $context['test_id'] === $test->id
                && $context['app_key'] === 'app-a'
                && $context['component_key'] === 'component-a'
                && $context['suite_key'] === 'suite-a'
                && $context['scenario_key'] === 'scenario-a'
                && $context['variant_key'] === 'variant-a'
                && ! str_contains(json_encode($context, JSON_THROW_ON_ERROR), 'example-sensitive-value')),
        );
    }

    public function test_it_persists_failed_steps_without_raw_error_content(): void
    {
        Log::spy();
        $identity = $this->identity();
        $runner = Mockery::mock(AcceptanceRunner::class);
        $runner->shouldReceive('run')->once()->andReturn(new RunResult(
            identity: $identity,
            scenarioName: 'Scenario A',
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

        $test = (new AcceptanceRunService($runner))->run($this->profile(), $identity, $this->scenario());

        $this->assertSame(TestStatusEnum::FAILED, $test->status);
        $this->assertSame('acceptance_step_failed', $test->error_code);
        $this->assertSame(TestStatusEnum::FAILED, $test->steps[0]->status);
        $this->assertSame('Step failed.', $test->steps[0]->error_message);
        $this->assertStringNotContainsString('example-sensitive-value', json_encode($test->load('steps')->toArray(), JSON_THROW_ON_ERROR));
        Log::shouldHaveReceived('warning')->once()->with(
            'tms.acceptance.step.failed',
            Mockery::on(fn (array $context): bool => $context['component_key'] === 'component-a'
                && $context['suite_key'] === 'suite-a'
                && $context['variant_key'] === 'variant-a'
                && $context['error_code'] === 'acceptance_step_failed'
                && ! str_contains(json_encode($context, JSON_THROW_ON_ERROR), 'example-sensitive-value')),
        );
    }

    public function test_it_normalizes_a_final_persistence_failure_and_keeps_the_tuple(): void
    {
        Log::spy();
        $identity = $this->identity();
        $runner = Mockery::mock(AcceptanceRunner::class);
        $runner->shouldReceive('run')->once()->andReturn(new RunResult(
            identity: $identity,
            scenarioName: 'Scenario A',
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
            $service->run($this->profile(), $identity, $this->scenario());
            $this->fail('Expected a normalized persistence failure.');
        } catch (AcceptanceExecutionException $exception) {
            $this->assertSame('acceptance_result_persistence_failed', $exception->errorCode);
            $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
        }

        $test = TestRun::query()->latest('id')->firstOrFail();
        $this->assertSame(TestStatusEnum::FAILED, $test->status);
        $this->assertSame('acceptance_result_persistence_failed', $test->error_code);
        $this->assertSame($this->identityColumns(), $test->only(array_keys($this->identityColumns())));
    }

    public function test_identity_migration_is_reversible_while_tests_are_empty(): void
    {
        $migration = require database_path('migrations/2026_10_08_000001_cut_over_acceptance_execution_identity.php');

        $migration->down();
        $this->assertFalse(Schema::hasColumn('tests', 'component_key'));
        $migration->up();

        foreach (['app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key'] as $column) {
            $this->assertTrue(Schema::hasColumn('tests', $column));
        }
        $columns = collect(DB::select("PRAGMA table_info('tests')"))->keyBy('name');
        foreach (['app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key'] as $column) {
            $this->assertSame(1, $columns[$column]->notnull);
        }
        $this->assertContains(
            'tests_acceptance_identity_created_index',
            collect(DB::select("PRAGMA index_list('tests')"))->pluck('name'),
        );
    }

    public function test_forward_identity_migration_refuses_non_empty_test_history_before_mutation(): void
    {
        $profile = $this->profile();
        $migration = require database_path('migrations/2026_10_08_000001_cut_over_acceptance_execution_identity.php');
        $migration->down();
        $profile->tests()->create([
            'name' => 'Existing scenario',
            'app_key' => 'app-a',
            'scenario_key' => 'scenario-a',
            'status' => TestStatusEnum::FINISHED,
        ]);

        try {
            $migration->up();
            $this->fail('Expected non-empty Test history to stop migration.');
        } catch (RuntimeException $exception) {
            $this->assertSame('acceptance_identity_migration_requires_empty_tests', $exception->getMessage());
        }
        $this->assertFalse(Schema::hasColumn('tests', 'component_key'));
        $this->assertDatabaseCount('tests', 1);
    }

    private function identity(): AcceptanceExecutionIdentity
    {
        return new AcceptanceExecutionIdentity('app-a', 'component-a', 'suite-a', 'scenario-a', 'variant-a');
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

    private function scenario(): AcceptanceScenario
    {
        return new class implements AcceptanceScenario
        {
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
                return new ScenarioMetadata(
                    ['persistence'],
                    ['in-memory'],
                    AutomationDisposition::AUTOMATED,
                    EvidenceMode::METADATA_ONLY,
                );
            }

            public function steps(TestContext $context): iterable
            {
                return [];
            }
        };
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
