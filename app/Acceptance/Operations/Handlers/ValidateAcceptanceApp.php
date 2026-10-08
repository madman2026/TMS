<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Modules\TargetModuleValidator;
use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;

final class ValidateAcceptanceApp implements AcceptanceOperationHandler
{
    public function __construct(private readonly TargetModuleValidator $validator) {}

    public function name(): string
    {
        return 'acceptance.app.validate';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        return new OperationResult(
            $this->name(),
            'succeeded',
            null,
            $correlationId,
            data: $this->validator->validate($request->parameters['module_name']),
        );
    }
}
