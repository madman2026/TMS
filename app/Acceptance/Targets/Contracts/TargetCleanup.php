<?php

namespace App\Acceptance\Targets\Contracts;

use App\Acceptance\Targets\Data\CleanupResult;
use App\Acceptance\Targets\Data\TargetContext;

interface TargetCleanup
{
    public function cleanup(TargetContext $context, int $timeoutMs): CleanupResult;
}
