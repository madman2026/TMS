<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Contracts;

use Modules\DK\Acceptance\Components\NotificationDelivery\Data\NotificationDeliveryEvent;

interface NotificationAuditOracle
{
    /** @return list<NotificationDeliveryEvent> */
    public function events(string $notificationKey): array;
}
