<?php

namespace App\Console\Commands;

/** Inspect only executable acceptance hierarchy rows. */
final class PlanAcceptanceCommand extends ListAcceptanceCommand
{
    protected $signature = 'acceptance:plan '.self::SELECTOR_OPTIONS.' '.self::COMMON_OPTIONS;

    protected function operation(): string
    {
        return 'acceptance.plan';
    }
}
