<?php

namespace App\Acceptance\Reporting;

use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Reporting\Data\AcceptanceStatusView;
use App\Models\AcceptanceBatchItem;
use App\Models\AcceptanceExecutionOperation;
use Throwable;

final class AcceptanceQueryService
{
    public function status(?string $operationId = null, ?int $batchId = null): AcceptanceStatusView
    {
        if (($operationId === null) === ($batchId === null)
            || ($operationId !== null && ! OperationResult::isUuid($operationId))
            || ($batchId !== null && $batchId < 1)) {
            throw AcceptanceReportingException::because('acceptance_report_query_invalid');
        }

        try {
            $operation = AcceptanceExecutionOperation::query()
                ->select([
                    'id', 'batch_id', 'correlation_id', 'state', 'lock_version', 'error_code',
                    'retryable', 'permanent', 'admin_action_required', 'started_at', 'finished_at',
                ])
                ->when($operationId !== null, fn ($query) => $query->whereKey($operationId))
                ->when($batchId !== null, fn ($query) => $query->where('batch_id', $batchId))
                ->with(['batch' => fn ($query) => $query->select([
                    'id', 'mode', 'state', 'lock_version', 'matched_count', 'executable_count',
                    'skipped_count', 'failure_count', 'started_at', 'finished_at',
                ])])
                ->first();
        } catch (Throwable) {
            throw AcceptanceReportingException::because('acceptance_reporting_failed');
        }
        if ($operation === null || $operation->batch === null) {
            throw AcceptanceReportingException::because('acceptance_report_not_found');
        }

        try {
            $stateCounts = AcceptanceBatchItem::query()
                ->where('batch_id', $operation->batch_id)
                ->selectRaw('state, count(*) as aggregate')
                ->groupBy('state')
                ->pluck('aggregate', 'state');
        } catch (Throwable) {
            throw AcceptanceReportingException::because('acceptance_reporting_failed');
        }

        $count = fn (BatchItemState $state): int => (int) ($stateCounts[$state->value] ?? 0);
        $batch = $operation->batch;

        try {
            return new AcceptanceStatusView(
                $operation->id,
                $batch->id,
                $operation->correlation_id,
                $operation->state,
                $batch->state,
                $batch->mode,
                $operation->lock_version,
                $batch->lock_version,
                $batch->matched_count,
                $batch->executable_count,
                $batch->skipped_count,
                $count(BatchItemState::PENDING),
                $count(BatchItemState::BLOCKED),
                $count(BatchItemState::QUEUED),
                $count(BatchItemState::RUNNING),
                $count(BatchItemState::PASSED),
                $count(BatchItemState::FAILED),
                $count(BatchItemState::CANCELLED),
                $batch->failure_count,
                $operation->error_code,
                $operation->retryable,
                $operation->permanent,
                $operation->admin_action_required,
                $operation->started_at ?? $batch->started_at,
                $operation->finished_at ?? $batch->finished_at,
            );
        } catch (Throwable) {
            throw AcceptanceReportingException::because('acceptance_reporting_failed');
        }
    }
}
