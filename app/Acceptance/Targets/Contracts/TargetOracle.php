<?php

namespace App\Acceptance\Targets\Contracts;

use App\Acceptance\Targets\Data\TargetContext;
use App\Acceptance\Targets\Data\TargetExecutionOutcome;
use App\Acceptance\Targets\Data\TargetOracleResult;

interface TargetOracle
{
    public function evaluate(TargetContext $context, TargetExecutionOutcome $outcome): TargetOracleResult;
}
