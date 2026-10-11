<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit\Acceptance\Coverage;

use App\Acceptance\Coverage\Enums\CoverageDisposition;
use Modules\DK\Acceptance\Coverage\SourceCaseMappings;
use PHPUnit\Framework\TestCase;

final class SourceCaseMappingsTest extends TestCase
{
    public function test_registry_preserves_the_complete_deterministic_corpus_projection(): void
    {
        $mappings = SourceCaseMappings::all();
        $sourceIds = [];
        $identities = [];
        $scenarioKeys = [];
        $assertionIds = [];
        $coveredReferences = 0;
        $minimumAssertions = PHP_INT_MAX;
        $maximumAssertions = 0;
        $dispositions = array_fill_keys(array_column(CoverageDisposition::cases(), 'value'), 0);
        $violations = [];

        foreach ($mappings as $mapping) {
            $sourceIds[] = $mapping->sourceCaseId;
            $dispositions[$mapping->disposition->value]++;

            $variantMarker = strrpos($mapping->sourceCaseId, '.v.');
            $replacement = [
                $mapping->replacementComponentKey,
                $mapping->replacementSuiteKey,
                $mapping->replacementScenarioKey,
                $mapping->replacementVariantKey,
            ];
            $valid = $mapping->disposition === CoverageDisposition::AUTOMATED_FULL
                && $mapping->componentKey === 'notification-delivery'
                && $mapping->version === 1
                && $mapping->reason === null
                && $replacement === [null, null, null, null]
                && $mapping->uncoveredAssertions === []
                && preg_match('/^nd\.[a-z0-9.-]+\.v\.[a-f0-9]{16}$/D', $mapping->sourceCaseId) === 1
                && preg_match('/^v\.[a-f0-9]{16}$/D', $mapping->variantKey) === 1
                && ! str_contains($mapping->scenarioKey, '.')
                && ! str_contains($mapping->sourceCaseId, 'example-sensitive-value')
                && $variantMarker !== false
                && str_replace('.', '-', substr($mapping->sourceCaseId, 0, $variantMarker)) === $mapping->scenarioKey
                && 'v.'.substr($mapping->sourceCaseId, $variantMarker + 3) === $mapping->variantKey;
            if (! $valid && count($violations) < 10) {
                $violations[] = $mapping->sourceCaseId;
            }

            $identity = implode("\0", $mapping->executableIdentity());
            if (isset($identities[$identity]) && count($violations) < 10) {
                $violations[] = 'duplicate-identity:'.$mapping->sourceCaseId;
            }
            $identities[$identity] = true;
            $scenarioKeys[$mapping->scenarioKey] = true;

            $sortedAssertions = $mapping->coveredAssertions;
            sort($sortedAssertions, SORT_STRING);
            if (($sortedAssertions !== $mapping->coveredAssertions
                || count(array_unique($mapping->coveredAssertions, SORT_STRING)) !== count($mapping->coveredAssertions))
                && count($violations) < 10) {
                $violations[] = 'assertion-order-or-duplicate:'.$mapping->sourceCaseId;
            }

            $assertionCount = count($mapping->coveredAssertions);
            $coveredReferences += $assertionCount;
            $minimumAssertions = min($minimumAssertions, $assertionCount);
            $maximumAssertions = max($maximumAssertions, $assertionCount);
            foreach ($mapping->coveredAssertions as $assertionId) {
                if (preg_match('/^a\.[a-f0-9]{20}$/D', $assertionId) !== 1 && count($violations) < 10) {
                    $violations[] = 'invalid-assertion-id:'.$mapping->sourceCaseId;
                }
                $assertionIds[$assertionId] = true;
            }
        }

        $sortedSourceIds = $sourceIds;
        sort($sortedSourceIds, SORT_STRING);

        $this->assertCount(24668, $mappings);
        $this->assertSame([], $violations);
        $this->assertSame($sortedSourceIds, $sourceIds);
        $this->assertCount(24668, array_unique($sourceIds, SORT_STRING));
        $this->assertCount(24668, $identities);
        $this->assertCount(139, $scenarioKeys);
        $this->assertCount(150724, $assertionIds);
        $this->assertSame(767744, $coveredReferences);
        $this->assertSame(21, $minimumAssertions);
        $this->assertSame(64, $maximumAssertions);
        $this->assertSame([
            CoverageDisposition::AUTOMATED_FULL->value => 24668,
            CoverageDisposition::AUTOMATED_PARTIAL->value => 0,
            CoverageDisposition::MERGED_EQUIVALENT->value => 0,
            CoverageDisposition::EXCLUDED_NO_RELIABLE_EXECUTOR->value => 0,
            CoverageDisposition::EXCLUDED_HUMAN_JUDGMENT->value => 0,
        ], $dispositions);
    }
}
