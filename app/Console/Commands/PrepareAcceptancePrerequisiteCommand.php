<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;
use InvalidArgumentException;

use function Laravel\Prompts\select;

final class PrepareAcceptancePrerequisiteCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:prerequisite:prepare
        {app?} {component?} {suite?} {scenario?} {variant?} {profile?}
        {--request-id=} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.prerequisite.request.prepare';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        $requestId = $this->optionalString('request-id');
        $hasIdentity = collect(['app', 'component', 'suite', 'scenario', 'variant', 'profile'])
            ->contains(fn (string $argument): bool => $this->argument($argument) !== null);

        if ($interactive && $requestId === null && ! $hasIdentity) {
            $mode = select('How do you want to prepare prerequisites?', [
                'new' => 'Create a new prerequisite request',
                'existing' => 'Inspect an existing prerequisite request',
            ]);
            if ($mode === 'existing') {
                return ['request_id' => $this->requiredString('request-id', 'Request ID', true, true)];
            }
        }

        if ($requestId !== null) {
            foreach (['app', 'component', 'suite', 'scenario', 'variant', 'profile'] as $argument) {
                if ($this->argument($argument) !== null) {
                    throw new InvalidArgumentException('cli_input_invalid');
                }
            }

            return ['request_id' => $requestId];
        }

        return [
            'app_key' => $this->requiredString('app', 'App key', $interactive),
            'component_key' => $this->requiredString('component', 'Component key', $interactive),
            'suite_key' => $this->requiredString('suite', 'Suite key', $interactive),
            'scenario_key' => $this->requiredString('scenario', 'Scenario key', $interactive),
            'variant_key' => $this->requiredString('variant', 'Variant key', $interactive),
            'profile_id' => $this->positiveInteger('profile', 'Profile ID', $interactive),
        ];
    }
}
