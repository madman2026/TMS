<?php

namespace App\Acceptance\Operations\Data;

use App\Acceptance\Targets\Data\TargetResourceLifecycleData;
use App\TestStatusEnum;
use InvalidArgumentException;

/** Only resolved identities and safe Test scalars cross the application boundary. */
final readonly class RunOperationData
{
    public function __construct(
        public TestStatusEnum $testStatus,
        public int $testId,
        public string $appKey,
        public string $componentKey,
        public string $suiteKey,
        public string $scenarioKey,
        public string $variantKey,
        public ?string $errorCode,
        public TargetResourceLifecycleData $resources,
    ) {
        if ($testId < 1 || ($errorCode !== null && ! in_array($errorCode, OperationResult::ERROR_CODES, true))) {
            throw new InvalidArgumentException('operation_result_invalid');
        }
        foreach ([$appKey, $componentKey, $suiteKey, $scenarioKey, $variantKey] as $key) {
            if (strlen($key) > 64 || ! preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key)) {
                throw new InvalidArgumentException('operation_result_invalid');
            }
        }
        if ($resources->appKey !== $appKey || $resources->componentKey !== $componentKey
            || $resources->suiteKey !== $suiteKey || $resources->scenarioKey !== $scenarioKey
            || $resources->variantKey !== $variantKey
            || ($resources->primaryErrorCode ?? $resources->cleanupErrorCode) !== $errorCode) {
            throw new InvalidArgumentException('operation_result_invalid');
        }
    }
}
