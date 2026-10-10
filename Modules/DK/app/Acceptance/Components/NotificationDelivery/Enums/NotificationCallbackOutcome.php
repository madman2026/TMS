<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Enums;

enum NotificationCallbackOutcome: string
{
    case Delivered = 'delivered';
    case Failed = 'failed';
}
