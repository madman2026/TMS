<?php

namespace App\Acceptance\Execution;

use RuntimeException;

final class AcceptanceExecutionException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        public readonly bool $retryable,
        public readonly bool $permanent,
        public readonly bool $adminActionRequired,
        public readonly ?string $cleanupErrorCode = null,
    ) {
        parent::__construct($errorCode);
    }

    public static function because(string $errorCode, ?string $cleanupErrorCode = null): self
    {
        [$retryable, $permanent, $adminActionRequired] = match ($errorCode) {
            'acceptance_batch_conflict', 'acceptance_attempt_stale', 'acceptance_batch_dispatch_failed',
            'acceptance_execution_persistence_failed', 'acceptance_attempt_timeout' => [true, false, true],
            'acceptance_batch_plan_changed', 'acceptance_configuration_invalid',
            'acceptance_batch_transition_invalid', 'acceptance_retry_not_safe' => [false, true, true],
            'acceptance_batch_not_found', 'acceptance_attempt_not_found', 'acceptance_batch_empty',
            'acceptance_batch_cancelled' => [false, true, false],
            default => [false, true, true],
        };

        return new self($errorCode, $retryable, $permanent, $adminActionRequired, $cleanupErrorCode);
    }
}
