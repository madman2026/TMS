<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final class AcceptanceExecutionStateChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly string $correlationId,
        public readonly string $operationId,
        public readonly int $batchId,
        public readonly ?int $itemId,
        public readonly ?int $attemptId,
        public readonly ?int $testId,
        public readonly string $entity,
        public readonly string $previousState,
        public readonly string $currentState,
        public readonly ?string $errorCode,
        public readonly ?bool $retryable,
        public readonly ?bool $permanent,
        public readonly bool $adminActionRequired,
    ) {}
}
