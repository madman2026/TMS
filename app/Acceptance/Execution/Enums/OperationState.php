<?php

namespace App\Acceptance\Execution\Enums;

enum OperationState: string
{
    case DRAFT = 'draft';
    case AWAITING_INPUT = 'awaiting_input';
    case AWAITING_APPROVAL = 'awaiting_approval';
    case READY = 'ready';
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELLING = 'cancelling';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function terminal(): bool
    {
        return in_array($this, [self::SUCCEEDED, self::FAILED, self::CANCELLED, self::EXPIRED], true);
    }
}
