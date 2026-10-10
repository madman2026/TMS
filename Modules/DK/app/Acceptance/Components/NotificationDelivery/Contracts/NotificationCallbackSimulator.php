<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Contracts;

use Modules\DK\Acceptance\Components\NotificationDelivery\Data\NotificationDeliveryEvent;
use Modules\DK\Acceptance\Components\NotificationDelivery\Enums\NotificationCallbackOutcome;

interface NotificationCallbackSimulator
{
    public function simulate(
        string $notificationKey,
        string $callbackKey,
        NotificationCallbackOutcome $outcome,
    ): NotificationDeliveryEvent;
}
