<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\CatalogOperationData;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Data\AcceptanceSelector;
use App\Services\AcceptancePlanner;

/** Adapt inspection parameters without resolving execution services or provider factories. */
class ListAcceptanceApps implements AcceptanceOperationHandler
{
    public function __construct(private readonly AcceptancePlanner $planner) {}

    public function name(): string
    {
        return 'acceptance.list';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        $options = $request->parameters;
        $options['evidence-mode'] = $options['evidence_mode'] ?? [];
        $options['limit'] = (string) ($options['limit'] ?? 1000);
        $plan = $this->planner->plan(AcceptanceSelector::fromOptions($options));

        return new OperationResult($this->name(), 'succeeded', null, $correlationId,
            data: CatalogOperationData::fromPlan($plan, $this->planning()));
    }

    protected function planning(): bool
    {
        return false;
    }
}
