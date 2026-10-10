<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;

final class CreateAcceptanceComponentCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:component:create {module?} {component?} {--apply} {--confirm} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.component.create';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        $apply = (bool) $this->option('apply');
        $parameters = [
            'module_name' => $this->requiredString('module', 'Module name', $interactive),
            'component_key' => $this->requiredString('component', 'Component key', $interactive),
            'dry_run' => ! $apply,
        ];
        if ($apply) {
            $this->confirmAction($interactive, 'Create the acceptance component now?');
        }

        return $parameters;
    }
}
