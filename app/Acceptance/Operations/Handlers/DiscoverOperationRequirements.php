<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Prerequisites\PrerequisiteException;
use App\Acceptance\Prerequisites\PrerequisiteService;

final class DiscoverOperationRequirements implements AcceptanceOperationHandler
{
    public function __construct(private readonly PrerequisiteService $prerequisites) {}

    public function name(): string
    {
        return 'acceptance.prerequisite.request.prepare';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        $parameters = $request->parameters;
        if (isset($parameters['request_id'])) {
            $data = $this->prerequisites->discover($parameters['request_id'], $correlationId, $this->name());
        } else {
            if ($operationId === null) {
                throw PrerequisiteException::because('prerequisite_persistence_failed');
            }
            $data = $this->prerequisites->prepare(
                [
                    'app_key' => $parameters['app_key'],
                    'component_key' => $parameters['component_key'],
                    'suite_key' => $parameters['suite_key'],
                    'scenario_key' => $parameters['scenario_key'],
                    'variant_key' => $parameters['variant_key'],
                ],
                (int) $parameters['profile_id'],
                $correlationId,
                $operationId,
                $this->name(),
            );
        }

        return new OperationResult($this->name(), 'succeeded', null, $correlationId, $operationId, data: $data);
    }
}
