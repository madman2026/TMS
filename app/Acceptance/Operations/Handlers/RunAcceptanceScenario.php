<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Operations\Data\RunOperationData;
use App\Acceptance\Prerequisites\PrerequisiteService;
use App\Acceptance\Targets\Data\TargetContext;
use App\Acceptance\Targets\Data\TargetExecutionOutcome;
use App\Acceptance\Targets\Data\TargetResourceLifecycleData;
use App\Acceptance\Targets\TargetResourceCoordinator;
use App\Models\Profile;
use App\Models\Test;
use App\Services\AcceptanceRunService;
use App\Services\AcceptanceVariantDispatcher;
use App\TestStatusEnum;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\RunOptions;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Throwable;

/** Resolve and execute one explicit hierarchy identity. */
final class RunAcceptanceScenario implements AcceptanceOperationHandler
{
    public function __construct(
        private readonly AcceptanceVariantDispatcher $dispatcher,
        private readonly AcceptanceRunService $runService,
        private readonly PrerequisiteService $prerequisites,
        private readonly TargetResourceCoordinator $resources,
    ) {}

    public function name(): string
    {
        return 'acceptance.run';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        $parameters = $request->parameters;
        try {
            $identity = new AcceptanceExecutionIdentity(
                $parameters['app_key'],
                $parameters['component_key'],
                $parameters['suite_key'],
                $parameters['scenario_key'],
                $parameters['variant_key'],
            );
            $scenario = $this->dispatcher->resolve($identity);
            $profile = Profile::query()->find($parameters['profile_id']);
            if ($profile === null) {
                return $this->rejection('acceptance_profile_not_found', $correlationId, $operationId);
            }
            $options = $this->options($parameters);
            $identityArray = [
                'app_key' => $identity->appKey,
                'component_key' => $identity->componentKey,
                'suite_key' => $identity->suiteKey,
                'scenario_key' => $identity->scenarioKey,
                'variant_key' => $identity->variantKey,
            ];
            $prerequisites = $this->prerequisites->executionData(
                $identityArray,
                (int) $profile->getKey(),
                $parameters['request_id'] ?? null,
                $correlationId,
                $this->name(),
            );
            if ($operationId === null) {
                return $this->rejection('acceptance_command_failed', $correlationId, null);
            }
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
                function () use (&$test, $profile, $identity, $scenario, $options): TargetExecutionOutcome {
                    try {
                        $test = $this->runService->run($profile, $identity, $scenario, $options);
                    } catch (AcceptanceExecutionException $exception) {
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
            );

            if (! $test instanceof Test) {
                $code = $lifecycle->primaryErrorCode ?? $lifecycle->cleanupErrorCode ?? 'acceptance_command_failed';

                return new OperationResult(
                    $this->name(),
                    $this->preExecutionStatus($lifecycle),
                    $code,
                    $correlationId,
                    $operationId,
                    data: $lifecycle,
                );
            }

            $code = $lifecycle->primaryErrorCode ?? $lifecycle->cleanupErrorCode;
            if ($code !== null && ($test->status === TestStatusEnum::FINISHED || $test->error_code !== $code)) {
                $test->update(['status' => TestStatusEnum::FAILED, 'error_code' => $code, 'data' => null]);
                $test->refresh();
            }
            $data = new RunOperationData(
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

            return new OperationResult($this->name(), $test->status === TestStatusEnum::FINISHED ? 'succeeded' : 'failed',
                $code, $correlationId, $operationId, data: $data);
        } catch (AcceptanceExecutionException $exception) {
            $code = in_array($exception->errorCode, ['acceptance_configuration_invalid', 'acceptance_browser_start_failed',
                'acceptance_scenario_failed', 'acceptance_result_persistence_failed'], true)
                ? $exception->errorCode : 'acceptance_command_failed';

            return new OperationResult(
                $this->name(),
                $code === 'acceptance_configuration_invalid' ? 'rejected' : 'failed',
                $code,
                $correlationId,
                $operationId,
            );
        }
    }

    private function rejection(string $code, string $correlationId, ?string $operationId): OperationResult
    {
        return new OperationResult($this->name(), 'rejected', $code, $correlationId, $operationId);
    }

    private function options(array $parameters): RunOptions
    {
        $browser = $parameters['browser'] ?? null;

        return new RunOptions(
            browser: is_string($browser) && $browser !== '' ? $browser : (string) config('core.acceptance.browser', 'chromium'),
            headless: ($parameters['headed'] ?? false) ? false : (bool) config('core.acceptance.headless', true),
            timeoutMs: $this->integerOption($parameters['timeout_ms'] ?? null, (int) config('core.acceptance.timeout_ms', 30_000), 1),
            slowMoMs: $this->integerOption($parameters['slow_mo_ms'] ?? null, (int) config('core.acceptance.slow_mo_ms', 0), 0),
        );
    }

    private function integerOption(int|string|null $value, int $default, int $minimum): int
    {
        if ($value === null || $value === '') {
            return $default;
        }
        $value = (string) $value;
        if (! ctype_digit($value) || (int) $value < $minimum) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        return (int) $value;
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
            'acceptance_configuration_invalid', 'acceptance_scenario_failed', 'acceptance_step_failed' => [false, true, false],
            'acceptance_browser_start_failed', 'acceptance_result_persistence_failed' => [true, false, true],
            default => [false, true, true],
        };

        return TargetExecutionOutcome::failed($code, $completed, $retryable, $permanent, $admin);
    }

    private function preExecutionStatus(TargetResourceLifecycleData $lifecycle): string
    {
        return in_array($lifecycle->primaryErrorCode, [
            'unsafe_target', 'target_not_ready', 'resource_unavailable',
        ], true) ? 'rejected' : 'failed';
    }
}
