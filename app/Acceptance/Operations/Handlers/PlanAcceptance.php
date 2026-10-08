<?php

namespace App\Acceptance\Operations\Handlers;

/** Same planner and fingerprint; only executable items enter the data snapshot. */
final class PlanAcceptance extends ListAcceptanceApps
{
    public function name(): string
    {
        return 'acceptance.plan';
    }

    protected function planning(): bool
    {
        return true;
    }
}
