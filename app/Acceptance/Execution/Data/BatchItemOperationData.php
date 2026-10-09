<?php

namespace App\Acceptance\Execution\Data;

use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Operations\Data\OperationResult;
use InvalidArgumentException;

final readonly class BatchItemOperationData
{
    public function __construct(
        public string $operationId,
        public int $batchId,
        public int $itemId,
        public ?int $parentItemId,
        public ?int $attemptId,
        public ?int $testId,
        public BatchItemState $state,
        public string $appKey,
        public string $componentKey,
        public string $suiteKey,
        public string $scenarioKey,
        public string $variantKey,
        public ?string $errorCode,
        public ?bool $retryable,
        public ?bool $permanent,
        public bool $adminActionRequired,
        public int $version = 1,
    ) {
        if ($version !== 1 || ! OperationResult::isUuid($operationId) || $batchId < 1 || $itemId < 1
            || ($parentItemId !== null && $parentItemId < 1) || ($attemptId !== null && $attemptId < 1)
            || ($testId !== null && $testId < 1)
            || ($errorCode !== null && ! in_array($errorCode, OperationResult::ERROR_CODES, true))) {
            throw new InvalidArgumentException('batch_item_operation_data_invalid');
        }
        foreach ([$appKey, $componentKey, $suiteKey, $scenarioKey, $variantKey] as $key) {
            if (strlen($key) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1) {
                throw new InvalidArgumentException('batch_item_operation_data_invalid');
            }
        }
    }
}
