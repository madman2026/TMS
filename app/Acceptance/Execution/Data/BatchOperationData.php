<?php

namespace App\Acceptance\Execution\Data;

use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\ExecutionMode;
use App\Acceptance\Execution\Enums\OperationState;
use App\Acceptance\Operations\Data\OperationResult;
use InvalidArgumentException;

final readonly class BatchOperationData
{
    public function __construct(
        public string $operationId,
        public int $batchId,
        public string $correlationId,
        public OperationState $operationState,
        public BatchState $batchState,
        public ExecutionMode $mode,
        public string $planFingerprint,
        public int $lockVersion,
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
        public ?string $errorCode = null,
        public ?bool $retryable = null,
        public ?bool $permanent = null,
        public bool $adminActionRequired = false,
        public int $version = 1,
    ) {
        $counts = [$batchId, $lockVersion, $matchedCount, $executableCount, $skippedCount, $pendingCount,
            $blockedCount, $queuedCount, $runningCount, $passedCount, $failedCount, $cancelledCount];
        if ($version !== 1 || $batchId < 1 || $lockVersion < 0 || min($counts) < 0
            || ! OperationResult::isUuid($operationId) || ! OperationResult::isUuid($correlationId)
            || preg_match('/^[a-f0-9]{64}$/D', $planFingerprint) !== 1
            || ($errorCode !== null && ! in_array($errorCode, OperationResult::ERROR_CODES, true))) {
            throw new InvalidArgumentException('batch_operation_data_invalid');
        }
    }
}
