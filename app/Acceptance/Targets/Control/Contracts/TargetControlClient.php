<?php

namespace App\Acceptance\Targets\Control\Contracts;

use App\Acceptance\Targets\Control\Data\TargetControlCapabilities;
use App\Acceptance\Targets\Control\Data\TargetControlLeaseRequest;
use App\Acceptance\Targets\Control\Data\TargetControlLeaseResult;

interface TargetControlClient
{
    public function capabilities(
        string $endpoint,
        string $secretHandle,
        TargetControlLeaseRequest $request,
    ): TargetControlCapabilities;

    public function acquireLease(
        string $endpoint,
        string $secretHandle,
        TargetControlLeaseRequest $request,
    ): TargetControlLeaseResult;

    public function inspectLease(
        string $endpoint,
        string $secretHandle,
        TargetControlLeaseRequest $request,
    ): TargetControlLeaseResult;

    public function cleanupLease(
        string $endpoint,
        string $secretHandle,
        TargetControlLeaseRequest $request,
    ): TargetControlLeaseResult;
}
