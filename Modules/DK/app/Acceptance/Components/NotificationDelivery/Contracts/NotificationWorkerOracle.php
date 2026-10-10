<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Contracts;

use Modules\DK\Acceptance\Components\NotificationDelivery\Enums\NotificationWorkerState;

interface NotificationWorkerOracle
{
    public function workerState(string $notificationKey): NotificationWorkerState;
}
