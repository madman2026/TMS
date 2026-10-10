<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;

final class CancelAcceptanceBatchCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:batch:cancel
        {batch?} {operation?} {lock-version?} {--confirm} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.batch.cancel';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        $parameters = [
            'batch_id' => $this->positiveInteger('batch', 'Batch ID', $interactive),
            'operation_id' => $this->requiredString('operation', 'Operation ID', $interactive),
            'expected_lock_version' => $this->nonNegativeInteger('lock-version', 'Lock version', $interactive),
        ];
        $this->confirmAction($interactive, 'Cancel this acceptance batch?');

        return $parameters;
    }
}
