<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Modules\Core\Contracts\AcceptanceExecutor;
use Modules\Core\Contracts\StepResult;
use Modules\Core\Data\BrowserExecutorData;
use Modules\Core\Data\BrowserStepData;
use Modules\Core\Data\ExecutorCapability;
use Modules\Core\Data\ExecutorRequest;
use Modules\Core\Data\ExecutorResult;
use Modules\Core\Data\RunResult;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Modules\Core\Exceptions\ExecutorException;
use Throwable;

final readonly class BrowserAcceptanceExecutor implements AcceptanceExecutor
{
    public function __construct(private AcceptanceRunner $runner) {}

    public function key(): string
    {
        return ExecutorCapability::BROWSER;
    }

    public function capabilities(): array
    {
        return [ExecutorCapability::browser()];
    }

    public function execute(ExecutorRequest $request): ExecutorResult
    {
        if (! $request->isBrowser()) {
            return ExecutorResult::unsupported($request, $this->key(), 'capability_unsupported');
        }

        try {
            $scenario = $request->scenario;
            $runOptions = $request->runOptions;
            $run = $this->runner->run($request->identity, $scenario, $runOptions);

            if ($run->identity !== $request->identity) {
                throw new ExecutorException('unsafe_executor_result');
            }

            $data = $this->normalize($run);

            if ($run->passed) {
                return ExecutorResult::succeeded($request, $this->key(), $data);
            }

            return ExecutorResult::failed(
                $request,
                $this->key(),
                'browser_execution_failed',
                false,
                true,
                false,
                $data,
            );
        } catch (AcceptanceExecutionException $exception) {
            return ExecutorResult::failed(
                $request,
                $this->key(),
                'browser_execution_failed',
                $exception->retryable,
                ! $exception->retryable,
                $exception->retryable,
            );
        } catch (ExecutorException $exception) {
            return ExecutorResult::unsafe($request, $this->key());
        } catch (Throwable $exception) {
            return ExecutorResult::failed(
                $request,
                $this->key(),
                'browser_execution_failed',
                false,
                true,
                false,
            );
        }
    }

    private function normalize(RunResult $run): BrowserExecutorData
    {
        $steps = [];
        foreach ($run->steps as $index => $step) {
            if (! $step instanceof StepResult) {
                throw new ExecutorException('unsafe_executor_result');
            }

            $steps[] = new BrowserStepData(
                position: $index + 1,
                name: $step->name,
                passed: $step->passed,
                errorCode: $step->errorCode,
                durationMs: $this->milliseconds($step->duration),
                critical: $step->critical,
            );
        }

        return new BrowserExecutorData(
            scenarioName: $run->scenarioName,
            passed: $run->passed,
            durationMs: $this->milliseconds($run->duration),
            steps: $steps,
        );
    }

    private function milliseconds(float $seconds): int
    {
        if (! is_finite($seconds) || $seconds < 0 || $seconds > 86_400) {
            throw new ExecutorException('unsafe_executor_result');
        }

        return (int) round($seconds * 1_000);
    }
}
