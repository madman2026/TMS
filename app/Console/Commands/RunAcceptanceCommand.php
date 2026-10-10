<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;
use InvalidArgumentException;

/** Execute one explicit acceptance hierarchy identity. */
final class RunAcceptanceCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:run
        {app?} {component?} {suite?} {scenario?} {variant?} {profile?}
        {--request-id=} {--browser=} {--headed} {--timeout=} {--slow-mo=}
        {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.run';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        $requestId = $this->optionalString('request-id');
        $browser = $this->optionalString('browser');
        $headed = (bool) $this->option('headed');
        $timeout = $this->optionalPositiveInteger('timeout');
        $slowMo = $this->optionalNonNegativeIntegerOption('slow-mo');

        if ($interactive) {
            $available = [];
            if ($requestId === null) {
                $available['request-id'] = 'Prerequisite request ID';
            }
            if ($browser === null) {
                $available['browser'] = 'Browser';
            }
            if (! $headed) {
                $available['headed'] = 'Headed browser mode';
            }
            if ($timeout === null) {
                $available['timeout'] = 'Timeout in milliseconds';
            }
            if ($slowMo === null) {
                $available['slow-mo'] = 'Slow motion delay in milliseconds';
            }
            foreach ($this->chooseOptionalFields('Configure optional runtime settings?', $available) as $option) {
                match ($option) {
                    'request-id' => $requestId = $this->requiredString('request-id', $available[$option], true, true),
                    'browser' => $browser = $this->requiredString('browser', $available[$option], true, true),
                    'headed' => $headed = true,
                    'timeout' => $timeout = $this->positiveInteger('timeout', $available[$option], true, true),
                    'slow-mo' => $slowMo = $this->nonNegativeInteger('slow-mo', $available[$option], true, true),
                };
            }
        }

        $parameters = [
            'app_key' => $this->requiredString('app', 'App key', $interactive),
            'component_key' => $this->requiredString('component', 'Component key', $interactive),
            'suite_key' => $this->requiredString('suite', 'Suite key', $interactive),
            'scenario_key' => $this->requiredString('scenario', 'Scenario key', $interactive),
            'variant_key' => $this->requiredString('variant', 'Variant key', $interactive),
            'profile_id' => $this->positiveInteger('profile', 'Profile ID', $interactive),
            'browser' => $browser,
            'headed' => $headed,
            'timeout_ms' => $timeout,
            'slow_mo_ms' => $slowMo,
        ];
        if ($requestId !== null) {
            $parameters['request_id'] = $requestId;
        }

        return $parameters;
    }

    private function optionalNonNegativeIntegerOption(string $name): ?int
    {
        $value = $this->option($name);
        if ($value === null || $value === '') {
            return null;
        }
        if ((! is_string($value) && ! is_int($value)) || ! ctype_digit((string) $value)) {
            throw new InvalidArgumentException('cli_input_invalid');
        }

        return (int) $value;
    }
}
