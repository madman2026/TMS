<?php

namespace App\Acceptance\Targets\Data;

use InvalidArgumentException;

final readonly class TargetExecutionOutcome
{
    public function __construct(
        public string $status,
        public ?string $errorCode,
        public bool $completed,
        public ?bool $retryable,
        public ?bool $permanent,
        public bool $adminActionRequired,
        public int $version = 1,
    ) {
        $validCode = $errorCode === null || (strlen($errorCode) <= 64
            && preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $errorCode) === 1);
        $validSuccess = $status === 'succeeded' && $errorCode === null && $completed
            && $retryable === null && $permanent === null && ! $adminActionRequired;
        $validFailure = $status === 'failed' && $errorCode !== null
            && $retryable !== null && $permanent !== null;
        $validCancelled = $status === 'cancelled' && $errorCode === null && ! $completed
            && $retryable === null && $permanent === null && ! $adminActionRequired;
        if ($version !== 1 || ! $validCode || (! $validSuccess && ! $validFailure && ! $validCancelled)) {
            throw new InvalidArgumentException('target_execution_outcome_invalid');
        }
    }

    public static function succeeded(): self
    {
        return new self('succeeded', null, true, null, null, false);
    }

    public static function failed(
        string $errorCode,
        bool $completed,
        bool $retryable,
        bool $permanent,
        bool $adminActionRequired,
    ): self {
        return new self('failed', $errorCode, $completed, $retryable, $permanent, $adminActionRequired);
    }

    public static function cancelled(): self
    {
        return new self('cancelled', null, false, null, null, false);
    }
}
