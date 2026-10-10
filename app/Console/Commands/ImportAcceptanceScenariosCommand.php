<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;

final class ImportAcceptanceScenariosCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:scenarios:import {module?} {--mappings=} {--apply} {--confirm} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.scenarios.import';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        $apply = (bool) $this->option('apply');
        $parameters = [
            'module_name' => $this->requiredString('module', 'Module name', $interactive),
            'mappings' => $this->sourceMappings(),
            'dry_run' => ! $apply,
        ];
        if ($apply) {
            $this->confirmAction($interactive, 'Apply the scenario import now?');
        }

        return $parameters;
    }
}
