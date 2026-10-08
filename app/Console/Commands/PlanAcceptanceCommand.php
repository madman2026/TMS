<?php

namespace App\Console\Commands;

use App\Data\AcceptanceSelector;

/** Client projection of the shared plan operation, preserving executable metadata rows. */
final class PlanAcceptanceCommand extends ListAcceptanceCommand
{
    protected $signature = 'acceptance:plan '.AcceptanceSelector::OPTIONS;

    protected function planning(): bool
    {
        return true;
    }
}
