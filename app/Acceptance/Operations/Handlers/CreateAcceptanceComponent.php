<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Modules\Contracts\TargetModuleCreator;
use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;

final class CreateAcceptanceComponent implements AcceptanceOperationHandler
{
    public function __construct(private readonly TargetModuleCreator $creator) {}

    public function name(): string
    {
        return 'acceptance.component.create';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        $parameters = $request->parameters;
        $data = $this->creator->createComponent(
            $parameters['module_name'],
            $parameters['component_key'],
            $parameters['dry_run'] ?? true,
        );

        return new OperationResult($this->name(), 'succeeded', null, $correlationId, $operationId, data: $data);
    }
}
