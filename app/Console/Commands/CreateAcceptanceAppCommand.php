<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;

final class CreateAcceptanceAppCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:app:create {module?} {app?} {--apply} {--confirm} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.app.create';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        $apply = (bool) $this->option('apply');
        $parameters = [
            'module_name' => $this->requiredString('module', 'Module name', $interactive),
            'app_key' => $this->requiredString('app', 'App key', $interactive),
            'dry_run' => ! $apply,
        ];
        if ($apply) {
            $this->confirmAction($interactive, 'Create the acceptance app now?');
        }

        return $parameters;
    }
}
