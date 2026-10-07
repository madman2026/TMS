<?php

namespace App\Console\Commands;

use App\Data\AcceptanceSelector;

/** Uses the same selection/fingerprint, projecting only executable metadata rows. */
final class PlanAcceptanceCommand extends ListAcceptanceCommand
{
    protected $signature = 'acceptance:plan '.AcceptanceSelector::OPTIONS;

    protected function planning(): bool
    {
        return true;
    }
}
