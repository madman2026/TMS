<?php

namespace App\Acceptance\Reporting;

use App\Acceptance\Reporting\Data\AcceptanceAttemptView;
use App\Acceptance\Reporting\Data\AcceptanceReport;
use App\Acceptance\Reporting\Data\AcceptanceReportItem;
use App\Models\AcceptanceBatchItem;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Throwable;

final class AcceptanceReportService
{
    public function __construct(
        private readonly AcceptanceQueryService $queries,
        private readonly AcceptanceEvidenceService $evidence,
        private readonly ConfigRepository $config,
    ) {}

    public function report(int $batchId, ?int $afterItemId = null, ?int $limit = null): AcceptanceReport
    {
        [$default, $maximum] = $this->pageLimits();
        $limit ??= $default;
        if ($batchId < 1 || $limit < 1 || $limit > $maximum || ($afterItemId !== null && $afterItemId < 1)) {
            throw AcceptanceReportingException::because('acceptance_report_query_invalid');
        }

        return $this->build($batchId, $afterItemId, $limit);
    }

    public function export(int $batchId): AcceptanceReport
    {
        $maximum = $this->configInt('max_export_rows');
        if ($batchId < 1 || $maximum < 1 || $maximum > 25_000) {
            throw AcceptanceReportingException::because('acceptance_configuration_invalid');
        }
        $count = AcceptanceBatchItem::query()->where('batch_id', $batchId)->count();
        if ($count > $maximum) {
            throw AcceptanceReportingException::because('acceptance_export_limit_exceeded');
        }

        return $this->build($batchId, null, max(1, $maximum));
    }

    private function build(int $batchId, ?int $afterItemId, int $limit): AcceptanceReport
    {
        $status = $this->queries->status(batchId: $batchId);
        if ($afterItemId !== null && ! AcceptanceBatchItem::query()
            ->where('batch_id', $batchId)->whereKey($afterItemId)->exists()) {
            throw AcceptanceReportingException::because('acceptance_report_query_invalid');
        }

        try {
            $rows = AcceptanceBatchItem::query()
                ->select([
                    'id', 'batch_id', 'ordinal', 'app_key', 'component_key', 'suite_key', 'scenario_key',
                    'variant_key', 'capability', 'catalog_version', 'state', 'attempt_count', 'error_code',
                    'cleanup_error_code', 'retryable', 'permanent', 'admin_action_required',
                    'queued_at', 'started_at', 'finished_at',
                ])
                ->where('batch_id', $batchId)
                ->when($afterItemId !== null, fn ($query) => $query->where('id', '>', $afterItemId))
                ->orderBy('id')
                ->limit($limit + 1)
                ->with([
                    'attempts' => fn ($query) => $query->select([
                        'id', 'item_id', 'test_id', 'attempt_number', 'state', 'executor_capability',
                        'executor_key', 'infrastructure_attempts', 'executor_entered', 'error_code',
                        'cleanup_error_code', 'retryable', 'permanent', 'admin_action_required',
                        'queued_at', 'started_at', 'finished_at',
                    ])->orderBy('attempt_number')->orderBy('id'),
                    'attempts.evidence' => fn ($query) => $query->select([
                        'id', 'attempt_id', 'type', 'reference_key', 'checksum_sha256', 'size_bytes',
                        'media_type', 'width', 'height', 'duration_ms', 'captured_at', 'available_until',
                    ])->orderBy('id'),
                ])
                ->get();
        } catch (Throwable) {
            throw AcceptanceReportingException::because('acceptance_reporting_failed');
        }

        try {
            $hasMore = $rows->count() > $limit;
            $rows = $rows->take($limit);
            $items = $rows->map(fn (AcceptanceBatchItem $item): AcceptanceReportItem => $this->item($item))->all();

            return new AcceptanceReport(
                $status,
                $items,
                $limit,
                $afterItemId,
                $hasMore && $rows->isNotEmpty() ? $rows->last()->id : null,
                $hasMore,
            );
        } catch (Throwable) {
            throw AcceptanceReportingException::because('acceptance_reporting_failed');
        }
    }

    private function item(AcceptanceBatchItem $item): AcceptanceReportItem
    {
        $attempts = $item->attempts->map(fn ($attempt): AcceptanceAttemptView => new AcceptanceAttemptView(
            $attempt->id,
            $attempt->attempt_number,
            $attempt->state,
            $attempt->test_id,
            $attempt->executor_capability,
            $attempt->executor_key,
            $attempt->infrastructure_attempts,
            $attempt->executor_entered,
            $attempt->error_code,
            $attempt->cleanup_error_code,
            $attempt->retryable,
            $attempt->permanent,
            $attempt->admin_action_required,
            $attempt->queued_at,
            $attempt->started_at,
            $attempt->finished_at,
            $attempt->evidence->map(fn ($entry) => $this->evidence->view($entry))->all(),
        ))->all();

        return new AcceptanceReportItem(
            $item->id,
            $item->ordinal,
            $item->app_key,
            $item->component_key,
            $item->suite_key,
            $item->scenario_key,
            $item->variant_key,
            $item->capability,
            $item->catalog_version,
            $item->state,
            $item->attempt_count,
            $item->error_code,
            $item->cleanup_error_code,
            $item->retryable,
            $item->permanent,
            $item->admin_action_required,
            $item->queued_at,
            $item->started_at,
            $item->finished_at,
            $attempts,
        );
    }

    /** @return array{int, int} */
    private function pageLimits(): array
    {
        $default = $this->configInt('default_page_size');
        $maximum = $this->configInt('max_page_size');
        if ($default < 1 || $default > $maximum || $maximum > 1000) {
            throw AcceptanceReportingException::because('acceptance_configuration_invalid');
        }

        return [$default, $maximum];
    }

    private function configInt(string $key): int
    {
        $value = $this->config->get('acceptance.reporting.'.$key);

        return is_int($value) ? $value : 0;
    }
}
