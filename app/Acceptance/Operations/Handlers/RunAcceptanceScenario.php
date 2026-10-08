<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Operations\Data\RunOperationData;
use App\Models\Profile;
use App\Services\AcceptanceRunService;
use App\Services\AcceptanceVariantDispatcher;
use App\TestStatusEnum;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\RunOptions;
use Modules\Core\Exceptions\AcceptanceExecutionException;

/** Resolve and execute one explicit hierarchy identity. */
final class RunAcceptanceScenario implements AcceptanceOperationHandler
{
    public function __construct(
        private readonly AcceptanceVariantDispatcher $dispatcher,
        private readonly AcceptanceRunService $runService,
    ) {}

    public function name(): string
    {
        return 'acceptance.run';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        $started = false;
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
            $started = true;
            $test = $this->runService->run($profile, $identity, $scenario, $options);
            $code = $test->error_code;
            if ($code !== null && ! in_array($code, OperationResult::ERROR_CODES, true)) {
                $code = 'acceptance_command_failed';
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
            );

            return new OperationResult($this->name(), $test->status === TestStatusEnum::FINISHED ? 'succeeded' : 'failed',
                $code, $correlationId, $operationId, data: $data);
        } catch (AcceptanceExecutionException $exception) {
            $code = in_array($exception->errorCode, ['acceptance_configuration_invalid', 'acceptance_browser_start_failed',
                'acceptance_scenario_failed', 'acceptance_result_persistence_failed'], true)
                ? $exception->errorCode : 'acceptance_command_failed';

            // Only option validation is rejection; the same code after RunService starts is failure.
            return new OperationResult($this->name(), ! $started && $code === 'acceptance_configuration_invalid'
                ? 'rejected' : 'failed', $code, $correlationId, $operationId);
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
}
