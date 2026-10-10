<?php

namespace App\Acceptance\Reporting\Data;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Coverage\Enums\CoverageDisposition;
use InvalidArgumentException;
use Throwable;

final readonly class CoverageSourceCaseView
{
    /** @var list<string> */
    public array $coveredAssertions;

    /** @var list<string> */
    public array $uncoveredAssertions;

    /** @param list<string> $coveredAssertions @param list<string> $uncoveredAssertions */
    public function __construct(
        public string $sourceCaseId,
        public CoverageDisposition $disposition,
        public ?string $componentKey,
        public ?string $suiteKey,
        public ?string $scenarioKey,
        public ?string $variantKey,
        public ?string $replacementComponentKey,
        public ?string $replacementSuiteKey,
        public ?string $replacementScenarioKey,
        public ?string $replacementVariantKey,
        public ?string $reason,
        array $coveredAssertions,
        array $uncoveredAssertions,
        public int $version = 1,
    ) {
        try {
            $mapping = new SourceCaseMapping(
                $sourceCaseId,
                $disposition,
                $componentKey,
                $suiteKey,
                $scenarioKey,
                $variantKey,
                $replacementComponentKey,
                $replacementSuiteKey,
                $replacementScenarioKey,
                $replacementVariantKey,
                $reason,
                $coveredAssertions,
                $uncoveredAssertions,
                $version,
            );
        } catch (Throwable) {
            throw new InvalidArgumentException('coverage_source_case_view_invalid');
        }
        $this->coveredAssertions = $mapping->coveredAssertions;
        $this->uncoveredAssertions = $mapping->uncoveredAssertions;
    }

    public static function fromMapping(SourceCaseMapping $mapping): self
    {
        return new self(
            $mapping->sourceCaseId,
            $mapping->disposition,
            $mapping->componentKey,
            $mapping->suiteKey,
            $mapping->scenarioKey,
            $mapping->variantKey,
            $mapping->replacementComponentKey,
            $mapping->replacementSuiteKey,
            $mapping->replacementScenarioKey,
            $mapping->replacementVariantKey,
            $mapping->reason,
            $mapping->coveredAssertions,
            $mapping->uncoveredAssertions,
            $mapping->version,
        );
    }
}
