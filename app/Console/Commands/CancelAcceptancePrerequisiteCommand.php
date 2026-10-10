<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;

final class CancelAcceptancePrerequisiteCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:prerequisite:request:cancel
        {request?} {lock-version?} {--confirm} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.prerequisite.request.cancel';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        $parameters = [
            'request_id' => $this->requiredString('request', 'Request ID', $interactive),
            'expected_lock_version' => $this->nonNegativeInteger('lock-version', 'Lock version', $interactive),
        ];
        $this->confirmAction($interactive, 'Cancel this prerequisite request?');

        return $parameters;
    }
}
