<?php

namespace App\Data;

use App\Exceptions\AcceptanceCatalogException;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;

/** OR within each exact-key dimension, AND between dimensions. */
final readonly class AcceptanceSelector
{
    public const OPTIONS = '{--app=*} {--scenario=*} {--variant=*} {--suite=*} {--capability=*} {--tag=*} {--disposition=*} {--evidence-mode=*} {--limit=1000}';

    public array $apps;

    public array $scenarios;

    public array $variants;

    public array $suites;

    public array $capabilities;

    public array $tags;

    public array $dispositions;

    public array $evidenceModes;

    public function __construct(
        array $apps = [],
        array $scenarios = [],
        array $variants = [],
        array $suites = [],
        array $capabilities = [],
        array $tags = [],
        array $dispositions = [],
        array $evidenceModes = [],
        public int $limit = 1000,
    ) {
        if ($limit < 1 || $limit > 1000) {
            throw AcceptanceCatalogException::because('acceptance_selector_invalid');
        }
        foreach ([
            'apps' => $apps, 'scenarios' => $scenarios, 'variants' => $variants,
            'suites' => $suites, 'capabilities' => $capabilities, 'tags' => $tags,
            'dispositions' => $dispositions, 'evidenceModes' => $evidenceModes,
        ] as $property => $values) {
            $copy = [];
            foreach ($values as $value) {
                $copy[] = ScenarioDescriptor::assertKey($value, 'acceptance_selector_invalid');
            }
            $copy = array_values(array_unique($copy, SORT_STRING));
            sort($copy, SORT_STRING);
            $this->$property = $copy;
        }
        if (array_diff($this->dispositions, array_column(AutomationDisposition::cases(), 'value'))
            || array_diff($this->evidenceModes, array_column(EvidenceMode::cases(), 'value'))) {
            throw AcceptanceCatalogException::because('acceptance_selector_invalid');
        }
    }

    public static function fromOptions(array $options): self
    {
        $arguments = [];
        foreach ([
            'app' => 'apps', 'scenario' => 'scenarios', 'variant' => 'variants',
            'suite' => 'suites', 'capability' => 'capabilities', 'tag' => 'tags',
            'disposition' => 'dispositions', 'evidence-mode' => 'evidenceModes',
        ] as $option => $property) {
            $values = $options[$option] ?? [];
            if (! is_array($values)) {
                throw AcceptanceCatalogException::because('acceptance_selector_invalid');
            }
            $arguments[$property] = $values;
        }
        $limit = $options['limit'] ?? '1000';
        if (! is_string($limit) || ! ctype_digit($limit) || strlen($limit) > 4) {
            throw AcceptanceCatalogException::because('acceptance_selector_invalid');
        }

        return new self(...[...$arguments, 'limit' => (int) $limit]);
    }

    public function matchesScenario(ScenarioDescriptor $descriptor): bool
    {
        foreach (['suites', 'capabilities', 'tags'] as $field) {
            if ($this->$field !== [] && array_intersect($this->$field, $descriptor->metadata->$field) === []) {
                return false;
            }
        }

        return ($this->dispositions === [] || in_array($descriptor->metadata->disposition->value, $this->dispositions, true))
            && ($this->evidenceModes === [] || in_array($descriptor->metadata->evidenceMode->value, $this->evidenceModes, true));
    }

    public function normalized(): array
    {
        return [
            'app' => $this->apps, 'scenario' => $this->scenarios, 'variant' => $this->variants,
            'suite' => $this->suites, 'capability' => $this->capabilities, 'tag' => $this->tags,
            'disposition' => $this->dispositions, 'evidence_mode' => $this->evidenceModes,
            'limit' => $this->limit,
        ];
    }
}
