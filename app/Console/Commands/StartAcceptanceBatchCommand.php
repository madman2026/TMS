<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;
use InvalidArgumentException;

use function Laravel\Prompts\select;

final class StartAcceptanceBatchCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:batch:start {profile?} {mode?} '
        .self::SELECTOR_OPTIONS.' {--prerequisites=} '.self::COMMON_OPTIONS;

    protected function operation(): string
    {
        return 'acceptance.batch.start';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        $mode = $this->argument('mode');
        if (($mode === null || $mode === '') && $interactive) {
            $mode = select('Execution mode', [
                'sync' => 'Synchronous',
                'async' => 'Asynchronous',
            ]);
        }
        if (! is_string($mode) || ! in_array($mode, ['sync', 'async'], true)) {
            throw new InvalidArgumentException('cli_input_invalid');
        }

        return [
            ...$this->selectorParameters($interactive),
            'profile_id' => $this->positiveInteger('profile', 'Profile ID', $interactive),
            'mode' => $mode,
            'prerequisite_references' => $this->prerequisiteReferences($interactive),
        ];
    }
}
