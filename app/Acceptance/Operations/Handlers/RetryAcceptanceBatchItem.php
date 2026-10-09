<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Execution\AcceptanceBatchService;
use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;

final class RetryAcceptanceBatchItem implements AcceptanceOperationHandler
{
    public function __construct(private readonly AcceptanceBatchService $batches) {}

    public function name(): string
    {
        return 'acceptance.batch.item.retry';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        if ($operationId === null) {
            return new OperationResult($this->name(), 'rejected', 'operation_request_invalid', $correlationId);
        }
        $data = $this->batches->retry(
            (int) $request->parameters['item_id'],
            (int) $request->parameters['expected_lock_version'],
            $correlationId,
            $operationId,
        );

        return new OperationResult(
            $this->name(),
            $data->state === BatchItemState::FAILED ? 'failed' : 'succeeded',
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
