<?php

namespace App\Console\Commands;

use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Operations\AcceptanceOperationService;
use App\Console\Acceptance\AcceptanceCommand;

use function Laravel\Prompts\multiselect;

final class AcceptanceCoverageCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:coverage
        {app?} {--after-source-case=} {--disposition=*} {--limit=} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.coverage';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        if ($interactive) {
            $available = [];
            if ($this->option('after-source-case') === null) {
                $available['after-source-case'] = 'After source case ID';
            }
            if ($this->option('disposition') === []) {
                $available['disposition'] = 'Coverage dispositions';
            }
            if ($this->option('limit') === null) {
                $available['limit'] = 'Page size';
            }
            foreach ($this->chooseOptionalFields('Configure optional coverage filters?', $available) as $option) {
                if ($option === 'disposition') {
                    $choices = array_column(CoverageDisposition::cases(), 'value', 'value');
                    $this->input->setOption('disposition', array_values(multiselect(
                        'Coverage dispositions',
                        $choices,
                        required: 'Select at least one disposition.',
                    )));

                    continue;
                }
                if ($option === 'limit') {
                    $this->input->setOption('limit', $this->positiveInteger('limit', 'Page size', true, true));

                    continue;
                }
                $this->input->setOption($option, $this->requiredString($option, $available[$option], true, true));
            }
        }

        return array_filter([
            'app_key' => $this->requiredString('app', 'App key', $interactive),
            'after_source_case_id' => $this->optionalString('after-source-case'),
            'disposition' => $this->option('disposition'),
            'limit' => $this->optionalPositiveInteger('limit'),
        ], fn (mixed $value): bool => $value !== null);
    }
}
