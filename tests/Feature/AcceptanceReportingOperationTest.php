<?php

namespace Tests\Feature;

use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\OperationState;
use App\Acceptance\Operations\AcceptanceOperationRegistry;
use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Reporting\Data\AcceptanceReport;
use App\Acceptance\Reporting\Data\AcceptanceStatusView;
use App\Models\AcceptanceBatch;
use App\Models\AcceptanceExecutionOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptanceReportingOperationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporting_handlers_are_registered_and_return_typed_safe_results(): void
    {
        $registry = $this->app->make(AcceptanceOperationRegistry::class);
        foreach (['acceptance.status', 'acceptance.report', 'acceptance.coverage'] as $operationName) {
            $this->assertTrue($registry->has($operationName));
            $this->assertSame($operationName, $registry->resolve($operationName)->name());
        }

        $batch = AcceptanceBatch::factory()->create([
            'state' => BatchState::RUNNING,
        ]);
        $operation = AcceptanceExecutionOperation::factory()->for($batch, 'batch')->create([
            'state' => OperationState::RUNNING,
        ]);
        $service = $this->app->make(AcceptanceOperationService::class);

        $correlationId = 'dcb1cf9d-207c-4a44-963b-000000000014';
        $status = $service->execute(new OperationRequest('acceptance.status', [
            'operation_id' => $operation->id,
        ], correlationId: $correlationId));
        $report = $service->execute(new OperationRequest('acceptance.report', [
            'batch_id' => $batch->id,
            'limit' => 1,
        ]));

        $this->assertSame('succeeded', $status->status);
        $this->assertSame($correlationId, $status->correlationId);
        $this->assertInstanceOf(AcceptanceStatusView::class, $status->data);
        $this->assertNull($status->operationId);
        $this->assertSame('succeeded', $report->status);
        $this->assertInstanceOf(AcceptanceReport::class, $report->data);
        $this->assertSame($batch->id, $report->data->status->batchId);
    }

    public function test_reporting_validation_and_domain_failures_are_normalized_by_the_operation_boundary(): void
    {
        $service = $this->app->make(AcceptanceOperationService::class);

        $invalid = $service->execute(new OperationRequest('acceptance.status', [
            'batch_id' => 1,
            'operation_id' => 'not-a-uuid',
        ]));
        $missing = $service->execute(new OperationRequest('acceptance.report', ['batch_id' => 99_999]));
        $coverage = $service->execute(new OperationRequest('acceptance.coverage', [
            'app_key' => 'unregistered-app',
        ]));
        $invalidLimit = $service->execute(new OperationRequest('acceptance.report', [
            'batch_id' => 1,
            'limit' => '5x',
        ]));
        $invalidDisposition = $service->execute(new OperationRequest('acceptance.coverage', [
            'app_key' => 'example-app',
            'disposition' => ['unknown'],
        ]));

        $this->assertSame('rejected', $invalid->status);
        $this->assertSame('operation_request_invalid', $invalid->errorCode);
        $this->assertSame('rejected', $missing->status);
        $this->assertSame('acceptance_report_not_found', $missing->errorCode);
        $this->assertSame('failed', $coverage->status);
        $this->assertSame('acceptance_coverage_mapping_invalid', $coverage->errorCode);
        $this->assertTrue($coverage->adminActionRequired);
        $this->assertNull($coverage->data);
        $this->assertSame('operation_request_invalid', $invalidLimit->errorCode);
        $this->assertSame('operation_request_invalid', $invalidDisposition->errorCode);
    }
}
