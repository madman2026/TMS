<?php

namespace App\Acceptance\Targets\Contracts;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Data\TargetContext;
use Modules\Core\Contracts\BrowserTargetRuntime;

interface TargetBrowserRuntimeResolver
{
    public function resolve(
        TargetContext $context,
        PrerequisiteExecutionData $prerequisites,
    ): BrowserTargetRuntime;
}
