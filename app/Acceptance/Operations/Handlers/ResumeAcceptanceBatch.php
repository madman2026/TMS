<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Execution\AcceptanceBatchService;
use App\Acceptance\Execution\Enums\OperationState;
use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;

final class ResumeAcceptanceBatch implements AcceptanceOperationHandler
{
    public function __construct(private readonly AcceptanceBatchService $batches) {}

    public function name(): string
    {
        return 'acceptance.batch.resume';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        if ($operationId === null) {
            return new OperationResult($this->name(), 'rejected', 'operation_request_invalid', $correlationId);
        }
        $data = $this->batches->resume(
            (int) $request->parameters['batch_id'],
            $operationId,
            (int) $request->parameters['expected_lock_version'],
            $request->parameters['prerequisite_references'] ?? [],
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
