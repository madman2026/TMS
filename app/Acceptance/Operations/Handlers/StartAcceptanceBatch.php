<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Execution\AcceptanceBatchService;
use App\Acceptance\Execution\Enums\ExecutionMode;
use App\Acceptance\Execution\Enums\OperationState;
use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Data\AcceptanceSelector;

final class StartAcceptanceBatch implements AcceptanceOperationHandler
{
    public function __construct(private readonly AcceptanceBatchService $batches) {}

    public function name(): string
    {
        return 'acceptance.batch.start';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        if ($operationId === null) {
            return new OperationResult($this->name(), 'rejected', 'operation_request_invalid', $correlationId);
        }
        $parameters = $request->parameters;
        $data = $this->batches->start(
            new AcceptanceSelector(
                apps: $parameters['app'] ?? [],
                scenarios: $parameters['scenario'] ?? [],
                variants: $parameters['variant'] ?? [],
                components: $parameters['component'] ?? [],
                suites: $parameters['suite'] ?? [],
                capabilities: $parameters['capability'] ?? [],
                tags: $parameters['tag'] ?? [],
                dispositions: $parameters['disposition'] ?? [],
                evidenceModes: $parameters['evidence_mode'] ?? [],
                limit: (int) ($parameters['limit'] ?? 1000),
            ),
            (int) $parameters['profile_id'],
            ExecutionMode::from($parameters['mode']),
            $parameters['prerequisite_references'] ?? [],
            $correlationId,
            $operationId,
        );
        $failed = $data->operationState === OperationState::FAILED;

        return new OperationResult(
            $this->name(),
            $failed ? 'failed' : 'succeeded',
            $data->errorCode,
            $correlationId,
            $operationId,
            $data->retryable,
            $data->permanent,
            $data->adminActionRequired,
            data: $data,
        );
    }
}
