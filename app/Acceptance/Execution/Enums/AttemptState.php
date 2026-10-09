<?php

namespace App\Acceptance\Execution\Enums;

enum AttemptState: string
{
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case ABANDONED = 'abandoned';

    public function terminal(): bool
    {
        return in_array($this, [self::SUCCEEDED, self::FAILED, self::ABANDONED], true);
    }
}
