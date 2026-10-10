<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Reporting\AcceptanceReportService;

final class GetAcceptanceReport implements AcceptanceOperationHandler
{
    public function __construct(private readonly AcceptanceReportService $reports) {}

    public function name(): string
    {
        return 'acceptance.report';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        $parameters = $request->parameters;
        $data = $this->reports->report(
            (int) $parameters['batch_id'],
            isset($parameters['after_item_id']) ? (int) $parameters['after_item_id'] : null,
            isset($parameters['limit']) ? (int) $parameters['limit'] : null,
        );

        return new OperationResult($this->name(), 'succeeded', null, $correlationId, data: $data);
    }
}
