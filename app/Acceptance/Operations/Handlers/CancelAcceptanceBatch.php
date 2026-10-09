<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Execution\AcceptanceBatchService;
use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;

final class CancelAcceptanceBatch implements AcceptanceOperationHandler
{
    public function __construct(private readonly AcceptanceBatchService $batches) {}

    public function name(): string
    {
        return 'acceptance.batch.cancel';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        if ($operationId === null) {
            return new OperationResult($this->name(), 'rejected', 'operation_request_invalid', $correlationId);
        }
        $data = $this->batches->cancel(
            (int) $request->parameters['batch_id'],
            $operationId,
            (int) $request->parameters['expected_lock_version'],
        );

        return new OperationResult($this->name(), 'succeeded', $data->errorCode, $correlationId, $operationId, data: $data);
    }
}
