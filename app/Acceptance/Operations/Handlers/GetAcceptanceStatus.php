<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Reporting\AcceptanceQueryService;

final class GetAcceptanceStatus implements AcceptanceOperationHandler
{
    public function __construct(private readonly AcceptanceQueryService $queries) {}

    public function name(): string
    {
        return 'acceptance.status';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        $data = $this->queries->status(
            $request->parameters['operation_id'] ?? null,
            isset($request->parameters['batch_id']) ? (int) $request->parameters['batch_id'] : null,
        );

        return new OperationResult($this->name(), 'succeeded', null, $correlationId, data: $data);
    }
}
