<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Test;
use App\TestStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Data\RunOptions;
use Modules\Core\Data\RunResult;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Modules\Core\Services\AcceptanceRunner;
use Throwable;

class AcceptanceRunService
{
    public function __construct(private readonly AcceptanceRunner $runner) {}

    public function run(
        Profile $profile,
        AcceptanceApp $app,
        AcceptanceScenario $scenario,
        ?RunOptions $options = null,
    ): Test {
        $test = $profile->tests()->create([
            'name' => $scenario->name(),
            'app_key' => $app->key(),
            'scenario_key' => $scenario->key(),
            'status' => TestStatusEnum::PENDING,
            'data' => null,
        ]);

        try {
            $result = $this->runner->run($app, $scenario, $options ?? $this->defaultOptions());
        } catch (AcceptanceExecutionException $exception) {
            $this->recordExecutionFailure($test, $exception);

            return $test->refresh();
        }

        try {
            $this->persistResult($test, $result);
        } catch (Throwable $exception) {
            $normalized = AcceptanceExecutionException::persistenceFailed($exception);
            $this->recordPersistenceFailure($test, $normalized);

            throw $normalized;
        }

        return $test->refresh();
    }

    protected function persistResult(Test $test, RunResult $result): void
    {
        DB::transaction(function () use ($test, $result): void {
            foreach ($result->steps as $step) {
                $test->steps()->create([
                    'name' => $step->name,
                    'duration' => (string) $step->duration,
                    'description' => $step->description,
                    'data' => null,
                    'status' => $step->passed
                        ? TestStatusEnum::FINISHED->value
                        : TestStatusEnum::FAILED->value,
                    'critical' => $step->critical,
                    'error_code' => $step->errorCode,
                    'error_message' => $step->passed ? null : 'Step failed.',
                ]);

                if (! $step->passed) {
                    Log::warning('tms.acceptance.step.failed', [
                        'test_id' => $test->getKey(),
                        'app_key' => $result->appKey,
                        'scenario_key' => $result->scenarioKey,
                        'step_name' => $step->name,
                        'error_code' => $step->errorCode,
                        'critical' => $step->critical,
                        'exception_class' => $step->exceptionClass,
                    ]);
                }
            }

            $test->update([
                'duration' => (string) $result->duration,
                'status' => $result->passed
                    ? TestStatusEnum::FINISHED
                    : TestStatusEnum::FAILED,
                'error_code' => $result->errorCode,
                'data' => null,
            ]);
        });
    }

    private function defaultOptions(): RunOptions
    {
        return new RunOptions(
            browser: (string) config('core.acceptance.browser', 'chromium'),
            headless: (bool) config('core.acceptance.headless', true),
            timeoutMs: (int) config('core.acceptance.timeout_ms', 30_000),
            slowMoMs: (int) config('core.acceptance.slow_mo_ms', 0),
        );
    }

    private function recordExecutionFailure(Test $test, AcceptanceExecutionException $exception): void
    {
        $test->update([
            'status' => TestStatusEnum::FAILED,
            'error_code' => $exception->errorCode,
            'data' => null,
        ]);

        Log::error('tms.acceptance.run.failed', [
            'test_id' => $test->getKey(),
            'app_key' => $test->app_key,
            'scenario_key' => $test->scenario_key,
            'error_code' => $exception->errorCode,
            'retryable' => $exception->retryable,
            'exception_class' => $exception->getPrevious() !== null
                ? $exception->getPrevious()::class
                : $exception::class,
        ]);
    }

    private function recordPersistenceFailure(Test $test, AcceptanceExecutionException $exception): void
    {
        $recorded = false;

        try {
            $recorded = $test->update([
                'status' => TestStatusEnum::FAILED,
                'error_code' => $exception->errorCode,
                'data' => null,
            ]);
        } catch (Throwable) {
            // Logging below is the final trace when the database cannot record the failure.
        }

        Log::log($recorded ? 'error' : 'critical', 'tms.acceptance.run.failed', [
            'test_id' => $test->getKey(),
            'app_key' => $test->app_key,
            'scenario_key' => $test->scenario_key,
            'error_code' => $exception->errorCode,
            'retryable' => $exception->retryable,
            'exception_class' => $exception->getPrevious() !== null
                ? $exception->getPrevious()::class
                : $exception::class,
        ]);
    }
}
