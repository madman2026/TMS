<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Reporting\AcceptanceReportingException;
use App\Acceptance\Reporting\CoverageTraceabilityService;

final class GetCoverageReport implements AcceptanceOperationHandler
{
    public function __construct(private readonly CoverageTraceabilityService $coverage) {}

    public function name(): string
    {
        return 'acceptance.coverage';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        $parameters = $request->parameters;
        $dispositions = array_map(
            fn (string $value): CoverageDisposition => CoverageDisposition::tryFrom($value)
                ?? throw AcceptanceReportingException::because('acceptance_report_query_invalid'),
            $parameters['disposition'] ?? [],
        );
        $data = $this->coverage->report(
            $parameters['app_key'],
            $parameters['after_source_case_id'] ?? null,
            isset($parameters['limit']) ? (int) $parameters['limit'] : null,
            $dispositions,
        );

        return new OperationResult($this->name(), 'succeeded', null, $correlationId, data: $data);
    }
}
