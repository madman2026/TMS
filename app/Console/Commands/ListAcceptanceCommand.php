<?php

namespace App\Console\Commands;

use App\Data\AcceptanceSelector;
use App\Exceptions\AcceptanceCatalogException;
use App\Services\AcceptancePlanner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/** JSON-only inspection; the planner never enters the execution pipeline. */
class ListAcceptanceCommand extends Command
{
    protected $signature = 'acceptance:list '.AcceptanceSelector::OPTIONS;

    public function handle(AcceptancePlanner $planner): int
    {
        try {
            $plan = $planner->plan(AcceptanceSelector::fromOptions($this->options()));
            $this->line(json_encode($plan->toArray($this->planning()), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        } catch (Throwable $failure) {
            $exception = $failure instanceof AcceptanceCatalogException
                ? $failure : AcceptanceCatalogException::because('acceptance_catalog_failed');
            Log::log($exception->rejected() ? 'warning' : 'error', 'tms.acceptance.catalog.failed', [
                'command' => $this->planning() ? 'acceptance:plan' : 'acceptance:list',
                'error_code' => $exception->errorCode,
            ]);
            $this->line(json_encode([
                'status' => $exception->rejected() ? 'rejected' : 'failed',
                'error_code' => $exception->errorCode,
            ], JSON_THROW_ON_ERROR));

            return $exception->rejected() ? self::INVALID : self::FAILURE;
        }
    }

    protected function planning(): bool
    {
        return false;
    }
}
