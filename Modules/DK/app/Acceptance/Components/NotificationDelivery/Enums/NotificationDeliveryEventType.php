<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Enums;

enum NotificationDeliveryEventType: string
{
    case ProviderSubmitted = 'provider_submitted';
    case CallbackDelivered = 'callback_delivered';
    case CallbackFailed = 'callback_failed';
}
