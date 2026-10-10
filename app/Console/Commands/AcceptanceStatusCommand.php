<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;
use InvalidArgumentException;

use function Laravel\Prompts\select;

final class AcceptanceStatusCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:status {--batch=} {--operation=} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.status';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        $batch = $this->optionalString('batch');
        $operation = $this->optionalString('operation');
        if ($batch !== null && $operation !== null) {
            throw new InvalidArgumentException('cli_input_invalid');
        }
        if ($batch === null && $operation === null && $interactive) {
            $kind = select('Find status by', [
                'batch' => 'Batch ID',
                'operation' => 'Operation ID',
            ]);
            if ($kind === 'batch') {
                return ['batch_id' => $this->positiveInteger('batch', 'Batch ID', true, true)];
            }

            return ['operation_id' => $this->requiredString('operation', 'Operation ID', true, true)];
        }
        if ($batch !== null) {
            if (! ctype_digit($batch) || (int) $batch < 1) {
                throw new InvalidArgumentException('cli_input_invalid');
            }

            return ['batch_id' => (int) $batch];
        }
        if ($operation !== null) {
            return ['operation_id' => $operation];
        }

        throw new InvalidArgumentException('cli_input_invalid');
    }
}
