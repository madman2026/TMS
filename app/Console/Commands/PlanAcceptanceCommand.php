<?php

namespace App\Console\Commands;

use App\Data\AcceptanceSelector;

/** Version-2 client projection of executable hierarchy rows. */
final class PlanAcceptanceCommand extends ListAcceptanceCommand
{
    protected $signature = 'acceptance:plan '.AcceptanceSelector::OPTIONS;

    protected function planning(): bool
    {
        return true;
    }
}
