<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Execution\AcceptanceExecutionService;
use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Prerequisites\PrerequisiteService;
use App\Acceptance\Targets\Data\TargetResourceLifecycleData;
use App\Models\Profile;
use App\Services\AcceptanceVariantDispatcher;
use App\TestStatusEnum;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\ExecutorTraceContext;
use Modules\Core\Data\RunOptions;
use Modules\Core\Exceptions\AcceptanceExecutionException;

/** Resolve and execute one explicit hierarchy identity. */
final class RunAcceptanceScenario implements AcceptanceOperationHandler
{
    public function __construct(
        private readonly AcceptanceVariantDispatcher $dispatcher,
        private readonly PrerequisiteService $prerequisites,
        private readonly AcceptanceExecutionService $execution,
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
            $data = $this->execution->execute(
                $profile,
                $identity,
                $scenario,
                $options,
                $prerequisites,
                $operationId,
                $correlationId,
                new ExecutorTraceContext($correlationId, $operationId),
            );

            if ($data instanceof TargetResourceLifecycleData) {
                $code = $data->primaryErrorCode ?? $data->cleanupErrorCode ?? 'acceptance_command_failed';

                return new OperationResult(
                    $this->name(),
                    $this->preExecutionStatus($data),
                    $code,
                    $correlationId,
                    $operationId,
                    data: $data,
                );
            }

            return new OperationResult($this->name(), $data->testStatus === TestStatusEnum::FINISHED ? 'succeeded' : 'failed',
                $data->errorCode, $correlationId, $operationId, data: $data);
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

    private function preExecutionStatus(TargetResourceLifecycleData $lifecycle): string
    {
        return in_array($lifecycle->primaryErrorCode, [
            'unsafe_target', 'target_not_ready', 'resource_unavailable',
        ], true) ? 'rejected' : 'failed';
    }
}
