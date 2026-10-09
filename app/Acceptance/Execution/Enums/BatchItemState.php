<?php

namespace App\Acceptance\Execution\Enums;

enum BatchItemState: string
{
    case PENDING = 'pending';
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case PASSED = 'passed';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';
    case BLOCKED = 'blocked';
    case CANCELLED = 'cancelled';

    public function terminal(): bool
    {
        return in_array($this, [self::PASSED, self::FAILED, self::SKIPPED, self::CANCELLED], true);
    }
}
