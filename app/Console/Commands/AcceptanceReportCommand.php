<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;

final class AcceptanceReportCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:report
        {batch?} {--after-item=} {--limit=} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.report';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        if ($interactive) {
            $available = [];
            if ($this->option('after-item') === null) {
                $available['after-item'] = 'After item ID';
            }
            if ($this->option('limit') === null) {
                $available['limit'] = 'Page size';
            }
            foreach ($this->chooseOptionalFields('Configure optional report pagination?', $available) as $option) {
                $this->input->setOption($option, $this->positiveInteger($option, $available[$option], true, true));
            }
        }

        return array_filter([
            'batch_id' => $this->positiveInteger('batch', 'Batch ID', $interactive),
            'after_item_id' => $this->optionalPositiveInteger('after-item'),
            'limit' => $this->optionalPositiveInteger('limit'),
        ], fn (mixed $value): bool => $value !== null);
    }
}
