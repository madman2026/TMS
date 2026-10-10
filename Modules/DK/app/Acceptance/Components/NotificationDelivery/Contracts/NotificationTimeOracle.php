<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Contracts;

interface NotificationTimeOracle
{
    public function currentMilliseconds(): int;
}
