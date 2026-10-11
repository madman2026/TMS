<?php

namespace App\Acceptance\Targets\Contracts;

use App\Acceptance\Targets\Data\TargetContext;
use Modules\Core\Contracts\BrowserTargetRuntime;

interface TargetBrowserRuntimeResolver
{
    public function resolve(TargetContext $context): BrowserTargetRuntime;
}
