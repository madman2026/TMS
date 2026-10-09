<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Modules\Core\Contracts\AcceptanceExecutor;
use Modules\Core\Contracts\ExecutorRegistry;
use Modules\Core\Data\ExecutorCapability;
use Modules\Core\Data\ExecutorRequest;
use Modules\Core\Data\ExecutorResult;
use Modules\Core\Enums\ExecutorResultStatus;
use Modules\Core\Exceptions\ExecutorException;
use Psr\Log\LoggerInterface;
use Throwable;

final class ExplicitExecutorRegistry implements ExecutorRegistry
{
    /** @var array<string, AcceptanceExecutor> */
    private array $executors = [];

    /** @var array<string, AcceptanceExecutor> */
    private array $capabilityOwners = [];

    /** @var array<string, string> */
    private array $capabilityOwnerKeys = [];

    /**
     * Registration is explicit and ordered; discovery and fallback routing are intentionally absent.
     *
     * @param  iterable<AcceptanceExecutor>  $executors
     */
    public function __construct(iterable $executors, private readonly LoggerInterface $logger)
    {
        foreach ($executors as $executor) {
            if (! $executor instanceof AcceptanceExecutor) {
                throw new ExecutorException('executor_registry_invalid');
            }

            $this->register($executor);
        }
    }

    public function register(AcceptanceExecutor $executor): void
    {
        try {
            $key = (new ExecutorCapability($executor->key()))->key;
            $capabilities = $executor->capabilities();

            if (isset($this->executors[$key]) || $capabilities === [] || ! array_is_list($capabilities)) {
                throw new ExecutorException('executor_registry_invalid');
            }

            $capabilityKeys = [];
            foreach ($capabilities as $capability) {
                if (! $capability instanceof ExecutorCapability
                    || isset($capabilityKeys[$capability->key])
                    || isset($this->capabilityOwners[$capability->key])) {
                    throw new ExecutorException('executor_registry_invalid');
                }

                $capabilityKeys[$capability->key] = true;
            }

            $this->executors[$key] = $executor;
            foreach (array_keys($capabilityKeys) as $capabilityKey) {
                $this->capabilityOwners[$capabilityKey] = $executor;
                $this->capabilityOwnerKeys[$capabilityKey] = $key;
            }
        } catch (ExecutorException $exception) {
            throw new ExecutorException('executor_registry_invalid');
        } catch (Throwable $exception) {
            throw new ExecutorException('executor_registry_invalid');
        }
    }

    public function for(ExecutorCapability $capability): ?AcceptanceExecutor
    {
        return $this->capabilityOwners[$capability->key] ?? null;
    }

    public function execute(ExecutorRequest $request): ExecutorResult
    {
        $executor = $this->for($request->capability);

        if ($executor === null) {
            return $this->record(ExecutorResult::unsupported($request, null, 'executor_not_found'));
        }

        $executorKey = $this->capabilityOwnerKeys[$request->capability->key];

        try {
            $result = $executor->execute($request);
        } catch (Throwable $exception) {
            $result = ExecutorResult::unsafe($request, $executorKey);
        }

        if (! $result->matches($request, $executorKey)) {
            $result = ExecutorResult::unsafe($request, $executorKey);
        }

        return $this->record($result);
    }

    private function record(ExecutorResult $result): ExecutorResult
    {
        $context = array_filter([
            'executor_key' => $result->executorKey,
            'capability' => $result->capability->key,
            'status' => $result->status->value,
            'error_code' => $result->errorCode,
            'retryable' => $result->retryable,
            'admin_action_required' => $result->adminActionRequired,
            'app_key' => $result->identity->appKey,
            'component_key' => $result->identity->componentKey,
            'suite_key' => $result->identity->suiteKey,
            'scenario_key' => $result->identity->scenarioKey,
            'variant_key' => $result->identity->variantKey,
            ...$result->trace->logContext(),
        ], static fn (mixed $value): bool => $value !== null);

        if ($result->status === ExecutorResultStatus::Succeeded) {
            $this->logger->info('tms.core.executor.completed', $context);
        } elseif ($result->status === ExecutorResultStatus::Unsupported) {
            $this->logger->warning('tms.core.executor.unsupported', $context);
        } elseif ($result->permanent === true || $result->adminActionRequired) {
            $this->logger->error('tms.core.executor.failed', $context);
        } else {
            $this->logger->warning('tms.core.executor.failed', $context);
        }

        return $result;
    }
}
