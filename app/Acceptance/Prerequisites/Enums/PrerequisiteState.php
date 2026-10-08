<?php

namespace App\Acceptance\Prerequisites\Enums;

enum PrerequisiteState: string
{
    case DRAFT = 'draft';
    case AWAITING_INPUT = 'awaiting_input';
    case AWAITING_APPROVAL = 'awaiting_approval';
    case READY = 'ready';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function terminal(): bool
    {
        return in_array($this, [self::CANCELLED, self::EXPIRED], true);
    }
}
