<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Prerequisites\Data\InputRequirement;
use App\Acceptance\Prerequisites\Data\PrerequisiteOperationData;
use App\Acceptance\Prerequisites\Enums\InputType;
use App\Console\Acceptance\AcceptanceCommand;
use InvalidArgumentException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

final class SubmitAcceptanceInputCommand extends AcceptanceCommand
{
    protected $signature = 'acceptance:prerequisite:input:submit
        {request?} {lock-version?} {--inputs=} {--interactive} {--json}';

    protected function operation(): string
    {
        return 'acceptance.prerequisite.input.submit';
    }

    protected function operationParameters(bool $interactive, AcceptanceOperationService $service): array
    {
        $requestId = $this->requiredString('request', 'Request ID', $interactive);
        $lockVersion = $this->nonNegativeInteger('lock-version', 'Lock version', $interactive);

        return [
            'request_id' => $requestId,
            'expected_lock_version' => $lockVersion,
            'inputs' => $interactive ? $this->interactiveInputs($service, $requestId) : $this->inputMap(),
        ];
    }

    /** @return array<string, array{source: string, value: string|int|bool|list<string>}> */
    private function interactiveInputs(AcceptanceOperationService $service, string $requestId): array
    {
        $result = spin(
            fn () => $service->execute(new OperationRequest(
                'acceptance.prerequisite.request.prepare',
                ['request_id' => $requestId],
            )),
            'Loading prerequisite requirements...',
        );
        if ($result->status !== 'succeeded' || ! $result->data instanceof PrerequisiteOperationData) {
            $this->stopWithResult($result);
        }

        $inputs = [];
        foreach ($result->data->requirements as $requirement) {
            if (! in_array($requirement->key, $result->data->missingInputKeys, true)) {
                continue;
            }
            if (! $requirement->required && ! confirm('Provide a value for '.$requirement->key.'?', default: false)) {
                continue;
            }
            $inputs[$requirement->key] = [
                'source' => $requirement->allowedSources[0]->value,
                'value' => $this->promptValue($requirement),
            ];
        }
        if ($inputs === []) {
            throw new InvalidArgumentException('cli_input_invalid');
        }

        return $inputs;
    }

    /** @return string|int|bool|list<string> */
    private function promptValue(InputRequirement $requirement): string|int|bool|array
    {
        if ($requirement->allowedValues !== null) {
            $choices = [];
            foreach ($requirement->allowedValues as $index => $value) {
                $choices[(string) $index] = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            }
            $selected = select($requirement->key, $choices);

            return $requirement->allowedValues[(int) $selected];
        }

        return match ($requirement->type) {
            InputType::BOOLEAN => confirm($requirement->key),
            InputType::INTEGER => (int) text(
                $requirement->key,
                required: $requirement->required,
                validate: fn (string $value): ?string => filter_var($value, FILTER_VALIDATE_INT) !== false
                    && ($requirement->minimum === null || (int) $value >= $requirement->minimum)
                    && ($requirement->maximum === null || (int) $value <= $requirement->maximum)
                        ? null : 'Enter a valid integer within the allowed range.',
            ),
            InputType::STRING_LIST => array_values(array_filter(array_map(
                'trim',
                preg_split('/\R/u', textarea($requirement->key, required: $requirement->required)) ?: [],
            ), fn (string $value): bool => $value !== '')),
            InputType::SECRET_REFERENCE => $this->promptSecretReference($requirement->key),
            InputType::STRING => text(
                $requirement->key,
                required: $requirement->required,
                validate: function (string $value) use ($requirement): ?string {
                    $length = mb_strlen($value, 'UTF-8');

                    return ($requirement->minimum === null || $length >= $requirement->minimum)
                        && ($requirement->maximum === null || $length <= $requirement->maximum)
                            ? null : 'Enter a value within the allowed length range.';
                },
            ),
        };
    }
}
