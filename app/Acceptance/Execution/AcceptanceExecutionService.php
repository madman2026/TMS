<?php

namespace App\Acceptance\Execution;

use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Operations\Data\RunOperationData;
use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Data\TargetContext;
use App\Acceptance\Targets\Data\TargetExecutionOutcome;
use App\Acceptance\Targets\Data\TargetResourceLifecycleData;
use App\Acceptance\Targets\TargetResourceCoordinator;
use App\Models\Profile;
use App\Models\Test;
use App\Services\AcceptanceRunService;
use App\TestStatusEnum;
use Closure;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\ExecutorTraceContext;
use Modules\Core\Data\RunOptions;
use Modules\Core\Exceptions\AcceptanceExecutionException as CoreExecutionException;
use Throwable;

final class AcceptanceExecutionService
{
    public function __construct(
        private readonly AcceptanceRunService $runService,
        private readonly TargetResourceCoordinator $resources,
    ) {}

    public function execute(
        Profile $profile,
        AcceptanceExecutionIdentity $identity,
        AcceptanceScenario $scenario,
        RunOptions $options,
        PrerequisiteExecutionData $prerequisites,
        string $operationId,
        string $correlationId,
        ?ExecutorTraceContext $trace = null,
        ?Closure $isCancelled = null,
        ?Closure $onExecutorEntry = null,
    ): RunOperationData|TargetResourceLifecycleData {
        $context = new TargetContext(
            $operationId,
            $correlationId,
            $prerequisites->requestId,
            $identity->appKey,
            $identity->componentKey,
            $identity->suiteKey,
            $identity->scenarioKey,
            $identity->variantKey,
            (int) $profile->getKey(),
        );
        $test = null;
        $lifecycle = $this->resources->execute(
            $context,
            $prerequisites,
            function () use (&$test, $profile, $identity, $scenario, $options, $trace, $onExecutorEntry): TargetExecutionOutcome {
                try {
                    if ($onExecutorEntry !== null && $onExecutorEntry() !== true) {
                        return $this->executionFailure('acceptance_attempt_stale', false);
                    }
                    $test = $this->runService->run($profile, $identity, $scenario, $options, $trace);
                } catch (CoreExecutionException $exception) {
                    return $this->executionFailure($exception->errorCode, false);
                } catch (Throwable) {
                    return $this->executionFailure('acceptance_command_failed', false);
                }

                if ($test->status === TestStatusEnum::FINISHED) {
                    return TargetExecutionOutcome::succeeded();
                }

                $code = $this->safeExecutionCode($test->error_code);

                return $this->executionFailure($code, $code === 'acceptance_step_failed');
            },
            $isCancelled,
        );

        if (! $test instanceof Test) {
            return $lifecycle;
        }

        $code = $lifecycle->primaryErrorCode ?? $lifecycle->cleanupErrorCode;
        if ($code !== null && ($test->status === TestStatusEnum::FINISHED || $test->error_code !== $code)) {
            $test->update(['status' => TestStatusEnum::FAILED, 'error_code' => $code, 'data' => null]);
            $test->refresh();
        }

        return new RunOperationData(
            $test->status,
            (int) $test->getKey(),
            $identity->appKey,
            $identity->componentKey,
            $identity->suiteKey,
            $identity->scenarioKey,
            $identity->variantKey,
            $code,
            $lifecycle,
        );
    }

    private function safeExecutionCode(?string $code): string
    {
        return $code !== null && in_array($code, OperationResult::ERROR_CODES, true)
            ? $code
            : 'acceptance_command_failed';
    }

    private function executionFailure(string $code, bool $completed): TargetExecutionOutcome
    {
        [$retryable, $permanent, $admin] = match ($code) {
            'acceptance_configuration_invalid', 'acceptance_scenario_failed', 'acceptance_step_failed',
            'browser_execution_failed', 'unsafe_executor_result', 'capability_unsupported' => [false, true, false],
            'acceptance_browser_start_failed', 'acceptance_result_persistence_failed',
            'http_transport_failed' => [true, false, true],
            default => [false, true, true],
        };

        return TargetExecutionOutcome::failed($code, $completed, $retryable, $permanent, $admin);
    }
}
