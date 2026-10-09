<?php

namespace App\Acceptance\Execution\Enums;

enum BatchState: string
{
    case PLANNED = 'planned';
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case CANCELLING = 'cancelling';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case COMPLETED_WITH_FAILURES = 'completed_with_failures';
    case FAILED = 'failed';
    case INTERRUPTED = 'interrupted';

    public function terminal(): bool
    {
        return in_array($this, [self::CANCELLED, self::COMPLETED, self::COMPLETED_WITH_FAILURES, self::FAILED], true);
    }
}
