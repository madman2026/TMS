<?php

namespace App\Jobs;

use App\Acceptance\Execution\AcceptanceBatchService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RunAcceptanceBatchItem implements ShouldQueue
{
    use Batchable, Queueable;

    public int $tries;

    public int $timeout;

    public bool $failOnTimeout = true;

    public function __construct(
        public readonly int $itemId,
        public readonly int $attemptId,
        public readonly string $operationId,
        public readonly string $executionToken,
    ) {
        $this->tries = (int) config('acceptance.queue.infrastructure_attempts', 3);
        $this->timeout = (int) config('acceptance.queue.job_timeout_seconds', 75);
    }

    public function handle(AcceptanceBatchService $batches): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $batches->executeItem($this->itemId, $this->attemptId, $this->operationId, $this->executionToken);
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return array_map('intval', config('acceptance.queue.backoff_seconds', [5, 15]));
    }

    public function failed(?Throwable $exception): void
    {
        try {
            app(AcceptanceBatchService::class)->failAttempt(
                $this->itemId,
                $this->attemptId,
                'acceptance_attempt_timeout',
            );
        } catch (Throwable) {
            // Recovery will reconcile a remaining lease if persistence is unavailable.
        }

        Log::error('tms.acceptance.batch.item_failed', [
            'operation_id' => $this->operationId,
            'item_id' => $this->itemId,
            'attempt_id' => $this->attemptId,
            'error_code' => 'acceptance_attempt_timeout',
        ]);
    }
}
