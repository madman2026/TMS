<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\BrowserFactory;
use Modules\Core\Contracts\StepResult;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\RunOptions;
use Modules\Core\Data\RunResult;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Playwright\Browser\BrowserContextInterface;
use Throwable;

class AcceptanceRunner
{
    public function __construct(private readonly BrowserFactory $browserFactory) {}

    public function run(
        AcceptanceApp $app,
        AcceptanceScenario $scenario,
        RunOptions $options,
    ): RunResult {
        $startedAt = microtime(true);
        $browserContext = null;
        $context = null;
        $result = null;
        $failure = null;

        try {
            $appKey = $this->validateKey($app->key());
            $scenarioKey = $this->validateKey($scenario->key());
            $scenarioName = $scenario->name();
            $browserContext = $this->createBrowserContext($options);
            $context = new TestContext($browserContext, $options->timeoutMs);
            $steps = [];
            $passed = true;
            $errorCode = null;

            foreach ($scenario->steps($context) as $step) {
                if (! $step instanceof StepResult) {
                    throw AcceptanceExecutionException::scenarioFailed(
                        new \UnexpectedValueException('Scenario steps must yield StepResult instances.'),
                    );
                }

                $steps[] = $step;

                if (! $step->passed) {
                    $passed = false;
                    $errorCode ??= $step->errorCode;

                    if ($step->critical) {
                        break;
                    }
                }
            }

            $result = new RunResult(
                appKey: $appKey,
                scenarioKey: $scenarioKey,
                scenarioName: $scenarioName,
                passed: $passed,
                duration: microtime(true) - $startedAt,
                steps: $steps,
                errorCode: $errorCode,
            );
        } catch (AcceptanceExecutionException $exception) {
            $failure = $exception;
        } catch (Throwable $exception) {
            $failure = AcceptanceExecutionException::scenarioFailed($exception);
        } finally {
            try {
                if ($context !== null) {
                    $context->close();
                } elseif ($browserContext !== null) {
                    $browserContext->close();
                }
            } catch (Throwable $exception) {
                $failure ??= AcceptanceExecutionException::scenarioFailed($exception);
            }
        }

        if ($failure !== null) {
            throw $failure;
        }

        return $result;
    }

    private function createBrowserContext(RunOptions $options): BrowserContextInterface
    {
        try {
            return $this->browserFactory->create($options);
        } catch (AcceptanceExecutionException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw AcceptanceExecutionException::browserStartFailed($exception);
        }
    }

    private function validateKey(string $key): string
    {
        if (! preg_match('/^[a-z0-9][a-z0-9._-]*$/', $key)) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        return $key;
    }
}
