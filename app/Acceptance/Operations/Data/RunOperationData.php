<?php

namespace App\Acceptance\Operations\Data;

use App\TestStatusEnum;
use InvalidArgumentException;

/** Only resolved identities and safe Test scalars cross the application boundary. */
final readonly class RunOperationData
{
    public function __construct(
        public TestStatusEnum $testStatus,
        public int $testId,
        public string $appKey,
        public string $scenarioKey,
        public ?string $errorCode,
    ) {
        if ($testId < 1 || ! preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $appKey)
            || ! preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $scenarioKey)
            || ($errorCode !== null && ! in_array($errorCode, OperationResult::ERROR_CODES, true))) {
            throw new InvalidArgumentException('operation_result_invalid');
        }
    }
}
