<?php

namespace App\Jobs;

use App\Acceptance\Execution\AcceptanceBatchService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RunAcceptanceBatch implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries;

    public int $timeout;

    public bool $failOnTimeout = true;

    public int $uniqueFor;

    public function __construct(
        public readonly int $batchId,
        public readonly string $operationId,
    ) {
        $this->tries = (int) config('acceptance.queue.infrastructure_attempts', 3);
        $this->timeout = (int) config('acceptance.queue.job_timeout_seconds', 75);
        $this->uniqueFor = (int) config('acceptance.queue.lock_seconds', 90);
    }

    public function handle(AcceptanceBatchService $batches): void
    {
        $batches->dispatchWave($this->batchId, $this->operationId);
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return array_map('intval', config('acceptance.queue.backoff_seconds', [5, 15]));
    }

    public function uniqueId(): string
    {
        return (string) $this->batchId;
    }

    public function failed(?Throwable $exception): void
    {
        try {
            app(AcceptanceBatchService::class)->failDispatch($this->batchId);
        } catch (Throwable) {
            // Recovery will reconcile any remaining leased attempts.
        }

        Log::error('tms.acceptance.batch.dispatch_failed', [
            'operation_id' => $this->operationId,
            'batch_id' => $this->batchId,
            'error_code' => 'acceptance_batch_dispatch_failed',
        ]);
    }
}
