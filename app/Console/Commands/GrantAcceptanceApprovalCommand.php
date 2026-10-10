<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;

final class GrantAcceptanceApprovalCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:prerequisite:approval:grant
        {request?} {lock-version?} {scope?} {--confirm} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.prerequisite.approval.grant';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        $parameters = [
            'request_id' => $this->requiredString('request', 'Request ID', $interactive),
            'expected_lock_version' => $this->nonNegativeInteger('lock-version', 'Lock version', $interactive),
            'scope' => $this->requiredString('scope', 'Approval scope', $interactive),
        ];
        $this->confirmAction($interactive, 'Grant this prerequisite approval?');

        return $parameters;
    }
}
