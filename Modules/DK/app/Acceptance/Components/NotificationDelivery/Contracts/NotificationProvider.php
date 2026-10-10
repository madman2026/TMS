<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Contracts;

use Modules\DK\Acceptance\Components\NotificationDelivery\Data\NotificationDeliveryRequest;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\NotificationSubmission;

interface NotificationProvider
{
    public function submit(NotificationDeliveryRequest $request): NotificationSubmission;
}
