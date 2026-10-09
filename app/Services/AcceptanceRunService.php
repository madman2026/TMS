<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Test;
use App\TestStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\ExecutorRegistry;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\BrowserExecutorData;
use Modules\Core\Data\ExecutorRequest;
use Modules\Core\Data\ExecutorTraceContext;
use Modules\Core\Data\RunOptions;
use Modules\Core\Data\RunResult;
use Modules\Core\Enums\ExecutorResultStatus;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Modules\Core\Services\AcceptanceRunner;
use Throwable;

class AcceptanceRunService
{
    public function __construct(
        private readonly AcceptanceRunner $runner,
        private readonly ?ExecutorRegistry $executors = null,
    ) {}

    public function run(
        Profile $profile,
        AcceptanceExecutionIdentity $identity,
        AcceptanceScenario $scenario,
        ?RunOptions $options = null,
        ?ExecutorTraceContext $trace = null,
    ): Test {
        $test = $profile->tests()->create([
            'name' => $scenario->name(),
            'app_key' => $identity->appKey,
            'component_key' => $identity->componentKey,
            'suite_key' => $identity->suiteKey,
            'scenario_key' => $identity->scenarioKey,
            'variant_key' => $identity->variantKey,
            'status' => TestStatusEnum::PENDING,
            'data' => null,
        ]);

        try {
            if ($this->executors === null) {
                $result = $this->runner->run($identity, $scenario, $options ?? $this->defaultOptions());
            } else {
                $request = ExecutorRequest::browser(
                    $identity,
                    $scenario,
                    $options ?? $this->defaultOptions(),
                    $this->traceWithTest($trace, (int) $test->getKey()),
                );
                $executorResult = $this->executors->execute($request);
                if ($executorResult->data instanceof BrowserExecutorData) {
                    $result = $executorResult->data;
                } else {
                    $test->update([
                        'status' => TestStatusEnum::FAILED,
                        'error_code' => $executorResult->errorCode ?? 'acceptance_command_failed',
                        'data' => null,
                    ]);

                    return $test->refresh();
                }

                if ($executorResult->status !== ExecutorResultStatus::Succeeded && $result->passed) {
                    $test->update([
                        'status' => TestStatusEnum::FAILED,
                        'error_code' => $executorResult->errorCode ?? 'acceptance_command_failed',
                        'data' => null,
                    ]);

                    return $test->refresh();
                }
            }
        } catch (AcceptanceExecutionException $exception) {
            $this->recordExecutionFailure($test, $exception);

            return $test->refresh();
        } catch (Throwable $exception) {
            $normalized = AcceptanceExecutionException::scenarioFailed($exception);
            $this->recordExecutionFailure($test, $normalized);

            return $test->refresh();
        }

        try {
            if ($result instanceof BrowserExecutorData) {
                $this->persistBrowserResult($test, $result);
            } else {
                $this->persistResult($test, $result);
            }
        } catch (Throwable $exception) {
            $normalized = AcceptanceExecutionException::persistenceFailed($exception);
            $this->recordPersistenceFailure($test, $normalized);

            throw $normalized;
        }

        return $test->refresh();
    }

    protected function persistResult(Test $test, RunResult $result): void
    {
        $this->persistNormalizedResult($test, $result);
    }

    protected function persistBrowserResult(Test $test, BrowserExecutorData $result): void
    {
        $this->persistNormalizedResult($test, $result);
    }

    private function persistNormalizedResult(Test $test, RunResult|BrowserExecutorData $result): void
    {
        DB::transaction(function () use ($test, $result): void {
            foreach ($result->steps as $step) {
                $test->steps()->create([
                    'name' => $step->name,
                    'duration' => (string) ($result instanceof BrowserExecutorData ? $step->durationMs / 1000 : $step->duration),
                    'description' => $result instanceof BrowserExecutorData ? null : $step->description,
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
                        ...$this->identityContext($result->identity),
                        'step_name' => $step->name,
                        'error_code' => $step->errorCode,
                        'critical' => $step->critical,
                        'exception_class' => $result instanceof BrowserExecutorData ? null : $step->exceptionClass,
                    ]);
                }
            }

            $test->update([
                'duration' => (string) ($result instanceof BrowserExecutorData ? $result->durationMs / 1000 : $result->duration),
                'status' => $result->passed
                    ? TestStatusEnum::FINISHED
                    : TestStatusEnum::FAILED,
                'error_code' => $result instanceof BrowserExecutorData
                    ? ($result->passed ? null : 'browser_execution_failed')
                    : $result->errorCode,
                'data' => null,
            ]);
        });
    }

    private function traceWithTest(?ExecutorTraceContext $trace, int $testId): ExecutorTraceContext
    {
        return new ExecutorTraceContext(
            correlationId: $trace?->correlationId,
            operationId: $trace?->operationId,
            batchId: $trace?->batchId,
            itemId: $trace?->itemId,
            attemptId: $trace?->attemptId,
            testId: $testId,
        );
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
            ...$this->testIdentityContext($test),
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
            // This log is the final trace when the database cannot record the failure.
        }

        Log::log($recorded ? 'error' : 'critical', 'tms.acceptance.run.failed', [
            'test_id' => $test->getKey(),
            ...$this->testIdentityContext($test),
            'error_code' => $exception->errorCode,
            'retryable' => $exception->retryable,
            'exception_class' => $exception->getPrevious() !== null
                ? $exception->getPrevious()::class
                : $exception::class,
        ]);
    }

    /** @return array{app_key: string, component_key: string, suite_key: string, scenario_key: string, variant_key: string} */
    private function identityContext(AcceptanceExecutionIdentity $identity): array
    {
        return [
            'app_key' => $identity->appKey,
            'component_key' => $identity->componentKey,
            'suite_key' => $identity->suiteKey,
            'scenario_key' => $identity->scenarioKey,
            'variant_key' => $identity->variantKey,
        ];
    }

    /** @return array{app_key: string, component_key: string, suite_key: string, scenario_key: string, variant_key: string} */
    private function testIdentityContext(Test $test): array
    {
        return [
            'app_key' => $test->app_key,
            'component_key' => $test->component_key,
            'suite_key' => $test->suite_key,
            'scenario_key' => $test->scenario_key,
            'variant_key' => $test->variant_key,
        ];
    }
}
