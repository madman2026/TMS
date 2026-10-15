<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Shared\Target\Contracts;

use App\Acceptance\Targets\Control\Data\TargetControlLeaseResult;
use App\Acceptance\Targets\Data\TargetContext;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\OperationsHealthVariant;

interface DKSyntheticTargetControl
{
    public function acquire(
        TargetContext $context,
        OperationsHealthVariant $variant,
        string $secretHandle,
    ): TargetControlLeaseResult;

    public function inspect(
        TargetContext $context,
        OperationsHealthVariant $variant,
        string $secretHandle,
    ): TargetControlLeaseResult;

    public function cleanup(
        TargetContext $context,
        OperationsHealthVariant $variant,
        string $secretHandle,
        int $timeoutMs,
    ): TargetControlLeaseResult;
}
