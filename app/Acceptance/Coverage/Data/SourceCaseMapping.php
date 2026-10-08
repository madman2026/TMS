<?php

namespace App\Acceptance\Coverage\Data;

use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Modules\TargetModuleException;
use App\Data\ScenarioDescriptor;

/** Immutable source-to-executable trace mapping owned by target source code. */
final readonly class SourceCaseMapping
{
    /** @var list<string> */
    public array $coveredAssertions;

    /** @var list<string> */
    public array $uncoveredAssertions;

    /**
     * @param  list<string>  $coveredAssertions
     * @param  list<string>  $uncoveredAssertions
     */
    public function __construct(
        public string $sourceCaseId,
        public CoverageDisposition $disposition,
        public ?string $componentKey = null,
        public ?string $suiteKey = null,
        public ?string $scenarioKey = null,
        public ?string $variantKey = null,
        public ?string $replacementComponentKey = null,
        public ?string $replacementSuiteKey = null,
        public ?string $replacementScenarioKey = null,
        public ?string $replacementVariantKey = null,
        public ?string $reason = null,
        array $coveredAssertions = [],
        array $uncoveredAssertions = [],
        public int $version = 1,
    ) {
        if ($version !== 1 || ! preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]{0,127}$/D', $sourceCaseId)
            || ($reason !== null && (strlen($reason) > 1024 || preg_match('/[\x00-\x1F\x7F]/', $reason)))) {
            throw TargetModuleException::because('acceptance_source_mapping_invalid');
        }

        $this->coveredAssertions = $this->assertions($coveredAssertions);
        $this->uncoveredAssertions = $this->assertions($uncoveredAssertions);
        $this->validateDisposition();
    }

    public function executable(): bool
    {
        return in_array($this->disposition, [
            CoverageDisposition::AUTOMATED_FULL,
            CoverageDisposition::AUTOMATED_PARTIAL,
        ], true);
    }

    /** @return array{string, string, string, string}|null */
    public function executableIdentity(): ?array
    {
        if (! $this->executable()) {
            return null;
        }

        return [$this->componentKey, $this->suiteKey, $this->scenarioKey, $this->variantKey];
    }

    /** @return array{string, string, string, string}|null */
    public function replacementIdentity(): ?array
    {
        if ($this->disposition !== CoverageDisposition::MERGED_EQUIVALENT) {
            return null;
        }

        return [
            $this->replacementComponentKey,
            $this->replacementSuiteKey,
            $this->replacementScenarioKey,
            $this->replacementVariantKey,
        ];
    }

    /** @param list<string> $assertions */
    private function assertions(array $assertions): array
    {
        if (count($assertions) > 256) {
            throw TargetModuleException::because('acceptance_source_mapping_invalid');
        }
        $copy = [];
        foreach ($assertions as $assertion) {
            if (! is_string($assertion)) {
                throw TargetModuleException::because('acceptance_source_mapping_invalid');
            }
            try {
                $copy[] = ScenarioDescriptor::assertKey($assertion, 'acceptance_source_mapping_invalid');
            } catch (\Throwable) {
                throw TargetModuleException::because('acceptance_source_mapping_invalid');
            }
        }
        if (count(array_unique($copy, SORT_STRING)) !== count($copy)) {
            throw TargetModuleException::because('acceptance_source_mapping_invalid');
        }

        return $copy;
    }

    private function validateDisposition(): void
    {
        $own = [$this->componentKey, $this->suiteKey, $this->scenarioKey, $this->variantKey];
        $replacement = [
            $this->replacementComponentKey,
            $this->replacementSuiteKey,
            $this->replacementScenarioKey,
            $this->replacementVariantKey,
        ];
        $reason = $this->reason !== null && trim($this->reason) !== '';

        if ($this->executable()) {
            $this->validateTuple($own);
            if (array_filter($replacement, fn (?string $value): bool => $value !== null) !== []) {
                throw TargetModuleException::because('acceptance_source_mapping_invalid');
            }
            if ($this->disposition === CoverageDisposition::AUTOMATED_PARTIAL
                && (! $reason || $this->coveredAssertions === [] || $this->uncoveredAssertions === [])) {
                throw TargetModuleException::because('acceptance_source_mapping_invalid');
            }

            return;
        }

        if (array_filter($own, fn (?string $value): bool => $value !== null) !== [] || ! $reason
            || $this->uncoveredAssertions === []) {
            throw TargetModuleException::because('acceptance_source_mapping_invalid');
        }

        if ($this->disposition === CoverageDisposition::MERGED_EQUIVALENT) {
            $this->validateTuple($replacement);
            if ($this->coveredAssertions === []) {
                throw TargetModuleException::because('acceptance_source_mapping_invalid');
            }
        } elseif (array_filter($replacement, fn (?string $value): bool => $value !== null) !== []) {
            throw TargetModuleException::because('acceptance_source_mapping_invalid');
        }
    }

    /** @param array{?string, ?string, ?string, ?string} $tuple */
    private function validateTuple(array $tuple): void
    {
        foreach ($tuple as $key) {
            try {
                ScenarioDescriptor::assertKey($key, 'acceptance_source_mapping_invalid');
            } catch (\Throwable) {
                throw TargetModuleException::because('acceptance_source_mapping_invalid');
            }
        }
    }
}
