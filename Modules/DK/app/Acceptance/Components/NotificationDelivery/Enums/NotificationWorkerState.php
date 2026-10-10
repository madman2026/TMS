<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Enums;

enum NotificationWorkerState: string
{
    case Queued = 'queued';
    case Processed = 'processed';
}
