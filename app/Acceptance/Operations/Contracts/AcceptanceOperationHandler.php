<?php

namespace App\Acceptance\Operations\Contracts;

use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;

/** A domain adapter; identity and trace values are established by the shared boundary. */
interface AcceptanceOperationHandler
{
    public function name(): string;

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult;
}
