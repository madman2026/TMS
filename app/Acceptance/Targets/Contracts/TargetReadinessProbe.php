<?php

namespace App\Acceptance\Targets\Contracts;

use App\Acceptance\Targets\Data\TargetContext;
use App\Acceptance\Targets\Data\TargetReadinessResult;

interface TargetReadinessProbe
{
    public function probe(TargetContext $context): TargetReadinessResult;
}
