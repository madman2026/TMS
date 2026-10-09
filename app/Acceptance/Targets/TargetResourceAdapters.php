<?php

namespace App\Acceptance\Targets;

use App\Acceptance\Targets\Contracts\TargetAccountResolver;
use App\Acceptance\Targets\Contracts\TargetCleanup;
use App\Acceptance\Targets\Contracts\TargetFixtureManager;
use App\Acceptance\Targets\Contracts\TargetOracle;
use App\Acceptance\Targets\Contracts\TargetReadinessProbe;

final readonly class TargetResourceAdapters
{
    public function __construct(
        public TargetReadinessProbe $readiness,
        public TargetAccountResolver $accounts,
        public TargetFixtureManager $fixtures,
        public TargetOracle $oracle,
        public TargetCleanup $cleanup,
    ) {}
}
