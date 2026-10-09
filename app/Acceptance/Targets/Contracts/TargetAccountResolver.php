<?php

namespace App\Acceptance\Targets\Contracts;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Data\ResourceProvisionResult;
use App\Acceptance\Targets\Data\TargetContext;

interface TargetAccountResolver
{
    public function resolve(TargetContext $context, PrerequisiteExecutionData $prerequisites): ResourceProvisionResult;
}
