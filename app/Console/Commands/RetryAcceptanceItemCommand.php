<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;

final class RetryAcceptanceItemCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:batch:item:retry
        {item?} {lock-version?} {--confirm} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.batch.item.retry';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        $parameters = [
            'item_id' => $this->positiveInteger('item', 'Item ID', $interactive),
            'expected_lock_version' => $this->nonNegativeInteger('lock-version', 'Lock version', $interactive),
        ];
        $this->confirmAction($interactive, 'Retry this acceptance batch item?');

        return $parameters;
    }
}
