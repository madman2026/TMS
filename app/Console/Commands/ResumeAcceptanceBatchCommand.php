<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;

final class ResumeAcceptanceBatchCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:batch:resume
        {batch?} {operation?} {lock-version?} {--prerequisites=} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.batch.resume';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        return [
            'batch_id' => $this->positiveInteger('batch', 'Batch ID', $interactive),
            'operation_id' => $this->requiredString('operation', 'Operation ID', $interactive),
            'expected_lock_version' => $this->nonNegativeInteger('lock-version', 'Lock version', $interactive),
            'prerequisite_references' => $this->prerequisiteReferences($interactive),
        ];
    }
}
