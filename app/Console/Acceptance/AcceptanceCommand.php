<?php

namespace App\Console\Acceptance;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Execution\Data\BatchPrerequisiteReference;
use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use JsonException;
use Laravel\Prompts\Prompt;
use RuntimeException;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

abstract class AcceptanceCommand extends Command
{
    protected const COMMON_OPTIONS = '{--interactive} {--json}';

    protected const SELECTOR_OPTIONS = '{--app=*} {--component=*} {--suite=*} {--scenario=*} {--variant=*} '
        .'{--capability=*} {--tag=*} {--disposition=*} {--evidence-mode=*} {--limit=1000}';

    protected const MAX_DOCUMENT_BYTES = 1_048_576;

    protected const MAX_INPUT_BYTES = 65_536;

    private ?OperationResult $preliminaryResult = null;

    public function __construct()
    {
        parent::__construct();
        $this->setDescription(match ($this->operation()) {
            'acceptance.list' => 'List acceptance catalog entries.',
            'acceptance.plan' => 'Plan executable acceptance catalog entries.',
            'acceptance.run' => 'Run one exact acceptance scenario variant.',
            'acceptance.app.create' => 'Preview or create an acceptance app module.',
            'acceptance.app.validate' => 'Validate an acceptance app module.',
            'acceptance.component.create' => 'Preview or create an acceptance component.',
            'acceptance.scenarios.import' => 'Preview or import acceptance scenarios.',
            'acceptance.prerequisite.request.prepare' => 'Prepare or inspect prerequisite requirements.',
            'acceptance.prerequisite.input.submit' => 'Submit prerequisite input values.',
            'acceptance.prerequisite.approval.grant' => 'Grant a prerequisite approval.',
            'acceptance.prerequisite.request.cancel' => 'Cancel a prerequisite request.',
            'acceptance.batch.start' => 'Start an acceptance execution batch.',
            'acceptance.batch.resume' => 'Resume an acceptance execution batch.',
            'acceptance.batch.item.retry' => 'Retry one acceptance batch item.',
            'acceptance.batch.cancel' => 'Cancel an acceptance execution batch.',
            'acceptance.status' => 'Show acceptance operation or batch status.',
            'acceptance.report' => 'Show a paginated acceptance batch report.',
            'acceptance.coverage' => 'Show paginated acceptance coverage.',
        });
    }

    abstract protected function operation(): string;

    /** @return array<string, mixed> */
    abstract protected function operationParameters(
        bool $interactive,
        AcceptanceOperationService $service,
    ): array;

    final public function handle(
        AcceptanceOperationService $service,
        OperationResultRenderer $renderer,
    ): int {
        $interactiveRequested = (bool) $this->option('interactive');
        $interactive = $interactiveRequested && ! (bool) $this->option('json') && $this->canPrompt();
        if ($interactiveRequested && ! $interactive) {
            return $renderer->renderClientFailure($this, 'cli_mode_invalid', false);
        }

        Prompt::cancelUsing(fn (): never => throw new RuntimeException('cli_interrupted'));
        if ($interactive) {
            intro($this->getDescription());
        }

        try {
            $parameters = $this->operationParameters($interactive, $service);
        } catch (Throwable $exception) {
            if ($this->preliminaryResult !== null) {
                return $renderer->render($this, $this->preliminaryResult, $interactive);
            }

            $code = in_array($exception->getMessage(), [
                'cli_input_invalid',
                'cli_confirmation_required',
                'cli_interrupted',
            ], true) ? $exception->getMessage() : 'cli_input_invalid';

            return $renderer->renderClientFailure($this, $code, $interactive);
        } finally {
            Prompt::cancelUsing(null);
        }

        $request = new OperationRequest($this->operation(), $parameters);
        $result = $interactive
            ? spin(fn (): OperationResult => $service->execute($request), 'Running acceptance operation...')
            : $service->execute($request);

        return $renderer->render($this, $result, $interactive);
    }

    protected function stopWithResult(OperationResult $result): never
    {
        $this->preliminaryResult = $result;

        throw new RuntimeException('cli_preliminary_result');
    }

    protected function requiredString(string $name, string $label, bool $interactive, bool $option = false): string
    {
        $value = $option ? $this->option($name) : $this->argument($name);
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }
        if (! $interactive) {
            throw new InvalidArgumentException('cli_input_invalid');
        }

        return trim(text(
            $label,
            required: 'This value is required.',
            validate: fn (string $answer): ?string => trim($answer) === '' ? 'Enter a non-empty value.' : null,
        ));
    }

    protected function optionalString(string $name, bool $option = true): ?string
    {
        $value = $option ? $this->option($name) : $this->argument($name);

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    protected function positiveInteger(string $name, string $label, bool $interactive, bool $option = false): int
    {
        $value = $option ? $this->option($name) : $this->argument($name);
        if ((! is_string($value) && ! is_int($value)) || ! ctype_digit((string) $value) || (int) $value < 1) {
            if (! $interactive) {
                throw new InvalidArgumentException('cli_input_invalid');
            }
            $value = text(
                $label,
                required: 'This value is required.',
                validate: fn (string $answer): ?string => ctype_digit($answer) && (int) $answer > 0
                    ? null : 'Enter a positive integer.',
            );
        }

        return (int) $value;
    }

    protected function nonNegativeInteger(
        string $name,
        string $label,
        bool $interactive,
        bool $option = false,
    ): int {
        $value = $option ? $this->option($name) : $this->argument($name);
        if ((! is_string($value) && ! is_int($value)) || ! ctype_digit((string) $value)) {
            if (! $interactive) {
                throw new InvalidArgumentException('cli_input_invalid');
            }
            $value = text(
                $label,
                required: 'This value is required.',
                validate: fn (string $answer): ?string => ctype_digit($answer)
                    ? null : 'Enter a non-negative integer.',
            );
        }

        return (int) $value;
    }

    protected function optionalPositiveInteger(string $name, bool $option = true): ?int
    {
        $value = $option ? $this->option($name) : $this->argument($name);
        if ($value === null || $value === '') {
            return null;
        }
        if ((! is_string($value) && ! is_int($value)) || ! ctype_digit((string) $value) || (int) $value < 1) {
            throw new InvalidArgumentException('cli_input_invalid');
        }

        return (int) $value;
    }

    protected function confirmAction(bool $interactive, string $label): void
    {
        if ($interactive) {
            if (! confirm($label, default: false)) {
                throw new RuntimeException('cli_confirmation_required');
            }

            return;
        }
        if (! (bool) $this->option('confirm')) {
            throw new RuntimeException('cli_confirmation_required');
        }
    }

    /** @return array<string, mixed>|list<mixed> */
    protected function jsonDocument(string $option, int $maximumBytes): array
    {
        $path = $this->option($option);
        if ((! is_string($path) || trim($path) === '')
            && (bool) $this->option('interactive')
            && ! (bool) $this->option('json')
            && $this->canPrompt()) {
            $path = text('JSON file path for --'.$option, required: 'A JSON file path is required.');
        }
        if (! is_string($path) || trim($path) === '' || ! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException('cli_input_invalid');
        }
        $size = @filesize($path);
        if (! is_int($size) || $size < 1 || $size > $maximumBytes) {
            throw new InvalidArgumentException('cli_input_invalid');
        }
        $contents = @file_get_contents($path);
        if (! is_string($contents) || strlen($contents) > $maximumBytes
            || ! mb_check_encoding($contents, 'UTF-8')) {
            throw new InvalidArgumentException('cli_input_invalid');
        }

        try {
            $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        } catch (JsonException) {
            throw new InvalidArgumentException('cli_input_invalid');
        }
        if (! is_array($decoded)) {
            throw new InvalidArgumentException('cli_input_invalid');
        }

        return $decoded;
    }

    /** @return list<SourceCaseMapping> */
    protected function sourceMappings(): array
    {
        $document = $this->jsonDocument('mappings', self::MAX_DOCUMENT_BYTES);
        if (! array_is_list($document)) {
            throw new InvalidArgumentException('cli_input_invalid');
        }

        return array_map(function (mixed $entry): SourceCaseMapping {
            $allowed = [
                'source_case_id', 'disposition', 'component_key', 'suite_key', 'scenario_key', 'variant_key',
                'replacement_component_key', 'replacement_suite_key', 'replacement_scenario_key',
                'replacement_variant_key', 'reason', 'covered_assertions', 'uncovered_assertions', 'version',
            ];
            if (! is_array($entry) || array_is_list($entry) || array_diff(array_keys($entry), $allowed) !== []
                || ! is_string($entry['source_case_id'] ?? null)
                || ! is_string($entry['disposition'] ?? null)) {
                throw new InvalidArgumentException('cli_input_invalid');
            }
            $disposition = CoverageDisposition::tryFrom($entry['disposition']);
            if ($disposition === null) {
                throw new InvalidArgumentException('cli_input_invalid');
            }

            return new SourceCaseMapping(
                $entry['source_case_id'],
                $disposition,
                $this->nullableString($entry, 'component_key'),
                $this->nullableString($entry, 'suite_key'),
                $this->nullableString($entry, 'scenario_key'),
                $this->nullableString($entry, 'variant_key'),
                $this->nullableString($entry, 'replacement_component_key'),
                $this->nullableString($entry, 'replacement_suite_key'),
                $this->nullableString($entry, 'replacement_scenario_key'),
                $this->nullableString($entry, 'replacement_variant_key'),
                $this->nullableString($entry, 'reason'),
                $this->stringList($entry, 'covered_assertions'),
                $this->stringList($entry, 'uncovered_assertions'),
                $this->version($entry),
            );
        }, $document);
    }

    /** @return list<BatchPrerequisiteReference> */
    protected function prerequisiteReferences(bool $interactive = false): array
    {
        $path = $this->option('prerequisites');
        if ($path === null || $path === '') {
            if (! $interactive || ! confirm('Load prerequisite references from a JSON file?', default: false)) {
                return [];
            }
        }
        $document = $this->jsonDocument('prerequisites', self::MAX_DOCUMENT_BYTES);
        if (! array_is_list($document)) {
            throw new InvalidArgumentException('cli_input_invalid');
        }

        return array_map(function (mixed $entry): BatchPrerequisiteReference {
            $required = ['app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key', 'request_id'];
            if (! is_array($entry) || array_is_list($entry)
                || array_diff(array_keys($entry), [...$required, 'version']) !== []) {
                throw new InvalidArgumentException('cli_input_invalid');
            }
            foreach ($required as $field) {
                if (! is_string($entry[$field] ?? null)) {
                    throw new InvalidArgumentException('cli_input_invalid');
                }
            }

            return new BatchPrerequisiteReference(
                $entry['app_key'],
                $entry['component_key'],
                $entry['suite_key'],
                $entry['scenario_key'],
                $entry['variant_key'],
                $entry['request_id'],
                $this->version($entry),
            );
        }, $document);
    }

    /** @return array<string, array{source: string, value: string|int|bool|list<string>}> */
    protected function inputMap(): array
    {
        $document = $this->jsonDocument('inputs', self::MAX_INPUT_BYTES);
        if (array_is_list($document) || $document === []) {
            throw new InvalidArgumentException('cli_input_invalid');
        }
        foreach ($document as $key => $payload) {
            $keys = is_array($payload) ? array_keys($payload) : [];
            sort($keys, SORT_STRING);
            if (! is_string($key) || preg_match('/^[a-z0-9][a-z0-9._-]{0,63}$/D', $key) !== 1
                || ! is_array($payload) || array_is_list($payload)
                || $keys !== ['source', 'value']
                || ! is_string($payload['source'])
                || ! in_array($payload['source'], ['literal', 'secret_reference'], true)) {
                throw new InvalidArgumentException('cli_input_invalid');
            }
            $value = $payload['value'];
            if (is_string($value) || is_int($value) || is_bool($value)) {
                continue;
            }
            if (! is_array($value) || ! array_is_list($value)
                || count(array_filter($value, 'is_string')) !== count($value)) {
                throw new InvalidArgumentException('cli_input_invalid');
            }
        }

        return $document;
    }

    /** @return array<string, mixed> */
    protected function selectorParameters(bool $interactive = false): array
    {
        if ($interactive) {
            $labels = [
                'app' => 'App keys',
                'component' => 'Component keys',
                'suite' => 'Suite keys',
                'scenario' => 'Scenario keys',
                'variant' => 'Variant keys',
                'capability' => 'Capabilities',
                'tag' => 'Tags',
                'disposition' => 'Automation dispositions',
                'evidence-mode' => 'Evidence modes',
                'limit' => 'Maximum matched items',
            ];
            $available = [];
            foreach ($labels as $option => $label) {
                if ($option === 'limit' || $this->option($option) === []) {
                    $available[$option] = $label;
                }
            }
            $selected = $this->chooseOptionalFields('Configure optional catalog filters?', $available);
            foreach ($selected as $option) {
                if ($option === 'limit') {
                    $this->input->setOption('limit', text(
                        'Maximum matched items',
                        default: (string) $this->option('limit'),
                        required: 'A positive integer is required.',
                        validate: fn (string $answer): ?string => ctype_digit($answer) && (int) $answer > 0
                            ? null : 'Enter a positive integer.',
                    ));

                    continue;
                }
                $values = textarea(
                    $labels[$option].' (one exact value per line)',
                    required: 'Enter at least one exact value.',
                    validate: fn (string $answer): ?string => $this->lineValues($answer) !== []
                        ? null : 'Enter at least one exact value.',
                );
                $this->input->setOption($option, $this->lineValues($values));
            }
        }

        $parameters = [];
        foreach (['app', 'component', 'suite', 'scenario', 'variant', 'capability', 'tag', 'disposition'] as $option) {
            $parameters[$option] = $this->option($option);
        }
        $parameters['evidence_mode'] = $this->option('evidence-mode');
        $parameters['limit'] = $this->option('limit');

        return $parameters;
    }

    protected function promptSecretReference(string $label): string
    {
        return password($label, required: 'A secret reference is required.');
    }

    /** @param array<string, string> $fields @return list<string> */
    protected function chooseOptionalFields(string $label, array $fields): array
    {
        if ($fields === []) {
            return [];
        }

        return array_values(multiselect(
            $label,
            $fields,
            hint: 'Use Space to select options, then press Enter. Select none to continue.',
        ));
    }

    private function canPrompt(): bool
    {
        if (! $this->input->isInteractive()) {
            return false;
        }
        foreach (['CI', 'GITHUB_ACTIONS', 'GITLAB_CI', 'TF_BUILD', 'BUILD_BUILDID'] as $variable) {
            $value = getenv($variable);
            if ($value !== false && $value !== '' && strtolower($value) !== 'false' && $value !== '0') {
                return false;
            }
        }

        return $this->laravel->runningUnitTests()
            || (defined('STDIN') && function_exists('stream_isatty') && stream_isatty(STDIN));
    }

    /** @return list<string> */
    private function lineValues(string $value): array
    {
        $values = array_map('trim', preg_split('/\R/u', $value) ?: []);

        return array_values(array_unique(array_filter($values, fn (string $item): bool => $item !== '')));
    }

    /** @param array<string, mixed> $entry */
    private function nullableString(array $entry, string $key): ?string
    {
        $value = Arr::get($entry, $key);
        if ($value !== null && ! is_string($value)) {
            throw new InvalidArgumentException('cli_input_invalid');
        }

        return $value;
    }

    /** @param array<string, mixed> $entry @return list<string> */
    private function stringList(array $entry, string $key): array
    {
        $value = Arr::get($entry, $key, []);
        if (! is_array($value) || ! array_is_list($value)
            || count(array_filter($value, 'is_string')) !== count($value)) {
            throw new InvalidArgumentException('cli_input_invalid');
        }

        return $value;
    }

    /** @param array<string, mixed> $entry */
    private function version(array $entry): int
    {
        $version = Arr::get($entry, 'version', 1);
        if (! is_int($version)) {
            throw new InvalidArgumentException('cli_input_invalid');
        }

        return $version;
    }
}
