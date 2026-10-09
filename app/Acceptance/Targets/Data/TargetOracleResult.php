<?php

namespace App\Acceptance\Targets\Data;

use InvalidArgumentException;

final readonly class TargetOracleResult
{
    public function __construct(
        public bool $passed,
        public ?string $errorCode,
        public ?bool $retryable,
        public ?bool $permanent,
        public bool $adminActionRequired,
        public int $version = 1,
    ) {
        $validPass = $passed && $errorCode === null && $retryable === null
            && $permanent === null && ! $adminActionRequired;
        $validFailure = ! $passed && $errorCode === 'oracle_failed' && $retryable === false
            && $permanent === true && ! $adminActionRequired;
        if ($version !== 1 || (! $validPass && ! $validFailure)) {
            throw new InvalidArgumentException('target_oracle_result_invalid');
        }
    }

    public static function passed(): self
    {
        return new self(true, null, null, null, false);
    }

    public static function failed(): self
    {
        return new self(false, 'oracle_failed', false, true, false);
    }
}
