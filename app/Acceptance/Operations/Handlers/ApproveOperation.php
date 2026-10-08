<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Prerequisites\PrerequisiteService;

final class ApproveOperation implements AcceptanceOperationHandler
{
    public function __construct(private readonly PrerequisiteService $prerequisites) {}

    public function name(): string
    {
        return 'acceptance.prerequisite.approval.grant';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        $parameters = $request->parameters;
        $data = $this->prerequisites->approve(
            $parameters['request_id'],
            $parameters['expected_lock_version'],
            $parameters['scope'],
            $correlationId,
            $this->name(),
        );

        return new OperationResult($this->name(), 'succeeded', null, $correlationId, $operationId, data: $data);
    }
}
