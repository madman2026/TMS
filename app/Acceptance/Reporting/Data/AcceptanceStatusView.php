<?php

namespace App\Acceptance\Reporting\Data;

use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\ExecutionMode;
use App\Acceptance\Execution\Enums\OperationState;
use App\Acceptance\Operations\Data\OperationResult;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class AcceptanceStatusView
{
    public function __construct(
        public string $operationId,
        public int $batchId,
        public string $correlationId,
        public OperationState $operationState,
        public BatchState $batchState,
        public ExecutionMode $mode,
        public int $operationLockVersion,
        public int $batchLockVersion,
        public int $matchedCount,
        public int $executableCount,
        public int $skippedCount,
        public int $pendingCount,
        public int $blockedCount,
        public int $queuedCount,
        public int $runningCount,
        public int $passedCount,
        public int $failedCount,
        public int $cancelledCount,
        public int $failureCount,
        public ?string $errorCode,
        public ?bool $retryable,
        public ?bool $permanent,
        public bool $adminActionRequired,
        public ?DateTimeImmutable $startedAt,
        public ?DateTimeImmutable $finishedAt,
        public int $version = 1,
    ) {
        $counts = [$batchId, $operationLockVersion, $batchLockVersion, $matchedCount, $executableCount,
            $skippedCount, $pendingCount, $blockedCount, $queuedCount, $runningCount, $passedCount,
            $failedCount, $cancelledCount, $failureCount];
        if ($version !== 1 || $batchId < 1 || min($counts) < 0
            || ! OperationResult::isUuid($operationId) || ! OperationResult::isUuid($correlationId)
            || ($errorCode !== null && ! in_array($errorCode, OperationResult::ERROR_CODES, true))
            || $matchedCount !== $skippedCount + $pendingCount + $blockedCount + $queuedCount
                + $runningCount + $passedCount + $failedCount + $cancelledCount
            || $executableCount !== $matchedCount - $skippedCount
            || $failureCount > $failedCount
            || ($startedAt !== null && $finishedAt !== null && $startedAt > $finishedAt)) {
            throw new InvalidArgumentException('acceptance_status_view_invalid');
        }
    }
}
