<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;

final class ValidateAcceptanceAppCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:app:validate {module?} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.app.validate';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        return ['module_name' => $this->requiredString('module', 'Module name', $interactive)];
    }
}
