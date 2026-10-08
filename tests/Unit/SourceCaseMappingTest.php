<?php

namespace Tests\Unit;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Modules\TargetModuleException;
use PHPUnit\Framework\TestCase;

class SourceCaseMappingTest extends TestCase
{
    public function test_all_five_dispositions_enforce_their_semantic_fields(): void
    {
        $full = new SourceCaseMapping('full', CoverageDisposition::AUTOMATED_FULL, 'component', 'suite', 'scenario', 'variant');
        $partial = new SourceCaseMapping('partial', CoverageDisposition::AUTOMATED_PARTIAL,
            'component', 'suite', 'scenario', 'edge', reason: 'partial',
            coveredAssertions: ['covered'], uncoveredAssertions: ['uncovered']);
        $merged = new SourceCaseMapping('merged', CoverageDisposition::MERGED_EQUIVALENT,
            replacementComponentKey: 'component', replacementSuiteKey: 'suite',
            replacementScenarioKey: 'scenario', replacementVariantKey: 'variant', reason: 'same behavior',
            coveredAssertions: ['covered'], uncoveredAssertions: ['duplicate']);
        $executor = new SourceCaseMapping('executor', CoverageDisposition::EXCLUDED_NO_RELIABLE_EXECUTOR,
            reason: 'unavailable', uncoveredAssertions: ['effect']);
        $human = new SourceCaseMapping('human', CoverageDisposition::EXCLUDED_HUMAN_JUDGMENT,
            reason: 'subjective', uncoveredAssertions: ['appearance']);

        $this->assertTrue($full->executable());
        $this->assertTrue($partial->executable());
        $this->assertSame(['component', 'suite', 'scenario', 'variant'], $merged->replacementIdentity());
        $this->assertFalse($executor->executable());
        $this->assertFalse($human->executable());
    }

    public function test_invalid_or_incomplete_mapping_is_rejected_with_a_stable_code(): void
    {
        foreach ([
            fn () => new SourceCaseMapping('../case', CoverageDisposition::AUTOMATED_FULL, 'c', 's', 'x', 'v'),
            fn () => new SourceCaseMapping('partial', CoverageDisposition::AUTOMATED_PARTIAL, 'c', 's', 'x', 'v'),
            fn () => new SourceCaseMapping('excluded', CoverageDisposition::EXCLUDED_HUMAN_JUDGMENT,
                'c', 's', 'x', 'v', reason: 'invalid', uncoveredAssertions: ['a']),
            fn () => new SourceCaseMapping('merged', CoverageDisposition::MERGED_EQUIVALENT,
                reason: 'missing replacement', coveredAssertions: ['a'], uncoveredAssertions: ['b']),
        ] as $factory) {
            try {
                $factory();
                $this->fail('Expected mapping rejection.');
            } catch (TargetModuleException $exception) {
                $this->assertSame('acceptance_source_mapping_invalid', $exception->errorCode);
            }
        }
    }
}
