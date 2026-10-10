<?php

namespace Tests\Unit;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Reporting\AcceptanceReportingException;
use App\Acceptance\Reporting\CoverageTraceabilityService;
use App\Contracts\AcceptanceComponentProvider;
use App\Contracts\AcceptanceCoverageProvider;
use App\Data\ComponentDescriptor;
use App\Data\ScenarioDescriptor;
use App\Data\SuiteDescriptor;
use App\Data\VariantDescriptor;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceCatalog;
use Illuminate\Config\Repository as ConfigRepository;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Tests\TestCase;

class CoverageTraceabilityServiceTest extends TestCase
{
    public function test_all_dispositions_reconcile_and_merged_sources_share_a_replacement(): void
    {
        $service = $this->service($this->mappings(), ['scenario-a', 'scenario-b']);

        $report = $service->report('app-a', limit: 10);

        $this->assertSame(6, $report->counts->total);
        $this->assertSame(1, $report->counts->automatedFull);
        $this->assertSame(1, $report->counts->automatedPartial);
        $this->assertSame(2, $report->counts->mergedEquivalent);
        $this->assertSame(1, $report->counts->excludedNoReliableExecutor);
        $this->assertSame(1, $report->counts->excludedHumanJudgment);
        $this->assertSame([
            'case-1-full', 'case-2-partial', 'case-3-merged',
            'case-4-merged', 'case-5-executor', 'case-6-human',
        ], array_column($report->cases, 'sourceCaseId'));
        $this->assertSame('scenario-a', $report->cases[2]->replacementScenarioKey);
        $this->assertSame('scenario-a', $report->cases[3]->replacementScenarioKey);
    }

    public function test_filter_and_cursor_change_only_the_page_not_reconciled_counts(): void
    {
        $service = $this->service($this->mappings(), ['scenario-a', 'scenario-b']);

        $first = $service->report('app-a', limit: 1, dispositions: [CoverageDisposition::MERGED_EQUIVALENT]);
        $second = $service->report(
            'app-a',
            $first->nextSourceCaseId,
            1,
            [CoverageDisposition::MERGED_EQUIVALENT],
        );

        $this->assertTrue($first->hasMore);
        $this->assertSame(6, $first->counts->total);
        $this->assertSame('case-3-merged', $first->cases[0]->sourceCaseId);
        $this->assertFalse($second->hasMore);
        $this->assertSame('case-4-merged', $second->cases[0]->sourceCaseId);
        $this->assertSame(6, $second->counts->total);
    }

    public function test_missing_direct_catalog_mapping_is_reported_as_incomplete(): void
    {
        $service = $this->service([
            new SourceCaseMapping('case-1', CoverageDisposition::AUTOMATED_FULL,
                'component-a', 'suite-a', 'scenario-a', 'default'),
        ], ['scenario-a', 'scenario-b']);

        try {
            $service->report('app-a');
            $this->fail('Expected incomplete coverage.');
        } catch (AcceptanceReportingException $exception) {
            $this->assertSame('acceptance_coverage_incomplete', $exception->errorCode);
        }
    }

    public function test_duplicate_sources_and_direct_identities_are_rejected(): void
    {
        $duplicates = [
            new SourceCaseMapping('case-1', CoverageDisposition::AUTOMATED_FULL,
                'component-a', 'suite-a', 'scenario-a', 'default'),
            new SourceCaseMapping('case-1', CoverageDisposition::AUTOMATED_FULL,
                'component-a', 'suite-a', 'scenario-b', 'default'),
        ];
        $sameIdentity = [
            new SourceCaseMapping('case-1', CoverageDisposition::AUTOMATED_FULL,
                'component-a', 'suite-a', 'scenario-a', 'default'),
            new SourceCaseMapping('case-2', CoverageDisposition::AUTOMATED_PARTIAL,
                'component-a', 'suite-a', 'scenario-a', 'default', reason: 'partial',
                coveredAssertions: ['covered'], uncoveredAssertions: ['missing']),
        ];

        foreach ([
            $this->service($duplicates, ['scenario-a', 'scenario-b']),
            $this->service($sameIdentity, ['scenario-a']),
        ] as $service) {
            try {
                $service->report('app-a');
                $this->fail('Expected duplicate coverage identity rejection.');
            } catch (AcceptanceReportingException $exception) {
                $this->assertSame('acceptance_coverage_mapping_invalid', $exception->errorCode);
            }
        }
    }

    public function test_missing_merged_replacement_and_hierarchy_only_provider_are_rejected(): void
    {
        $mappings = [
            new SourceCaseMapping('case-1', CoverageDisposition::AUTOMATED_FULL,
                'component-a', 'suite-a', 'scenario-a', 'default'),
            new SourceCaseMapping(
                'case-2',
                CoverageDisposition::MERGED_EQUIVALENT,
                replacementComponentKey: 'component-a',
                replacementSuiteKey: 'suite-a',
                replacementScenarioKey: 'scenario-missing',
                replacementVariantKey: 'default',
                reason: 'equivalent',
                coveredAssertions: ['covered'],
                uncoveredAssertions: ['duplicate'],
            ),
        ];

        try {
            $this->service($mappings, ['scenario-a'])->report('app-a');
            $this->fail('Expected a missing merged replacement.');
        } catch (AcceptanceReportingException $exception) {
            $this->assertSame('acceptance_coverage_incomplete', $exception->errorCode);
        }

        $metadata = new ScenarioMetadata(['browser'], [], AutomationDisposition::AUTOMATED, EvidenceMode::METADATA_ONLY);
        $provider = new class($metadata) implements AcceptanceComponentProvider
        {
            public function __construct(private readonly ScenarioMetadata $metadata) {}

            public function key(): string
            {
                return 'app-a';
            }

            public function catalogVersion(): string
            {
                return 'v1';
            }

            public function components(): iterable
            {
                yield new ComponentDescriptor('component-a');
            }

            public function suites(): iterable
            {
                yield new SuiteDescriptor('suite-a', 'component-a');
            }

            public function scenarios(): iterable
            {
                yield new ScenarioDescriptor('scenario-a', 'component-a', 'suite-a', $this->metadata);
            }

            public function variants(string $scenarioKey): iterable
            {
                yield new VariantDescriptor('default');
            }

            public function resolveScenario(string $componentKey, string $suiteKey, string $scenarioKey, string $variantKey): ?AcceptanceScenario
            {
                return null;
            }
        };

        try {
            $this->serviceForProvider($provider)->report('app-a');
            $this->fail('Expected a hierarchy-only provider rejection.');
        } catch (AcceptanceReportingException $exception) {
            $this->assertSame('acceptance_coverage_mapping_invalid', $exception->errorCode);
        }
    }

    public function test_export_rejects_a_reconciled_source_set_above_its_ceiling(): void
    {
        $service = $this->service($this->mappings(), ['scenario-a', 'scenario-b'], 5);

        try {
            $service->export('app-a');
            $this->fail('Expected the coverage export ceiling to be enforced.');
        } catch (AcceptanceReportingException $exception) {
            $this->assertSame('acceptance_export_limit_exceeded', $exception->errorCode);
        }
    }

    public function test_approved_source_corpus_is_reconciled_inside_fixed_time_and_memory_bounds(): void
    {
        $mappings = [new SourceCaseMapping('case-00000', CoverageDisposition::AUTOMATED_FULL,
            'component-a', 'suite-a', 'scenario-a', 'default')];
        for ($index = 1; $index < 24_668; $index++) {
            $mappings[] = new SourceCaseMapping(
                'case-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT),
                CoverageDisposition::MERGED_EQUIVALENT,
                replacementComponentKey: 'component-a',
                replacementSuiteKey: 'suite-a',
                replacementScenarioKey: 'scenario-a',
                replacementVariantKey: 'default',
                reason: 'equivalent',
                coveredAssertions: ['behavior'],
                uncoveredAssertions: ['duplicate'],
            );
        }
        $service = $this->service($mappings, ['scenario-a']);
        $memory = memory_get_usage(true);
        $started = hrtime(true);

        $report = $service->report('app-a', limit: 1);

        $elapsedSeconds = (hrtime(true) - $started) / 1_000_000_000;
        $incrementalMemory = max(0, memory_get_peak_usage(true) - $memory);
        $this->assertSame(24_668, $report->counts->total);
        $this->assertLessThan(5.0, $elapsedSeconds);
        $this->assertLessThan(64 * 1024 * 1024, $incrementalMemory);
    }

    public function test_source_traversal_observes_one_overflow_entry_then_stops(): void
    {
        $mappings = [new SourceCaseMapping('case-00000', CoverageDisposition::AUTOMATED_FULL,
            'component-a', 'suite-a', 'scenario-a', 'default')];
        for ($index = 1; $index <= 24_668; $index++) {
            $mappings[] = $this->merged('case-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT));
        }

        try {
            $this->service($mappings, ['scenario-a'], maxSourceCases: 24_668)->report('app-a');
            $this->fail('Expected the source traversal ceiling to be enforced.');
        } catch (AcceptanceReportingException $exception) {
            $this->assertSame('acceptance_export_limit_exceeded', $exception->errorCode);
        }
    }

    /** @param list<SourceCaseMapping> $mappings @param list<string> $scenarios */
    private function service(
        array $mappings,
        array $scenarios,
        int $maxExportRows = 25_000,
        int $maxSourceCases = 25_000,
    ): CoverageTraceabilityService {
        $metadata = new ScenarioMetadata(['browser'], [], AutomationDisposition::AUTOMATED, EvidenceMode::METADATA_ONLY);
        $provider = new class($mappings, $scenarios, $metadata) implements AcceptanceCoverageProvider
        {
            /** @param list<SourceCaseMapping> $mappings @param list<string> $scenarios */
            public function __construct(
                private readonly array $mappings,
                private readonly array $scenarioKeys,
                private readonly ScenarioMetadata $metadata,
            ) {}

            public function key(): string
            {
                return 'app-a';
            }

            public function catalogVersion(): string
            {
                return 'v1';
            }

            public function components(): iterable
            {
                yield new ComponentDescriptor('component-a');
            }

            public function suites(): iterable
            {
                yield new SuiteDescriptor('suite-a', 'component-a');
            }

            public function scenarios(): iterable
            {
                foreach ($this->scenarioKeys as $scenario) {
                    yield new ScenarioDescriptor($scenario, 'component-a', 'suite-a', $this->metadata);
                }
            }

            public function variants(string $scenarioKey): iterable
            {
                if (in_array($scenarioKey, $this->scenarioKeys, true)) {
                    yield new VariantDescriptor('default');
                }
            }

            public function resolveScenario(
                string $componentKey,
                string $suiteKey,
                string $scenarioKey,
                string $variantKey,
            ): ?AcceptanceScenario {
                return null;
            }

            public function sourceCaseMappings(): iterable
            {
                return $this->mappings;
            }
        };

        return $this->serviceForProvider($provider, $maxExportRows, $maxSourceCases);
    }

    private function serviceForProvider(
        AcceptanceComponentProvider $provider,
        int $maxExportRows = 25_000,
        int $maxSourceCases = 25_000,
    ): CoverageTraceabilityService {
        $registry = new AcceptanceAppRegistry;
        $registry->register($provider);
        $config = new ConfigRepository(['acceptance' => ['reporting' => [
            'default_page_size' => 50,
            'max_page_size' => 250,
            'max_export_rows' => $maxExportRows,
            'max_source_cases' => $maxSourceCases,
        ]]]);

        return new CoverageTraceabilityService($registry, new AcceptanceCatalog($registry), $config);
    }

    /** @return list<SourceCaseMapping> */
    private function mappings(): array
    {
        return [
            new SourceCaseMapping('case-1-full', CoverageDisposition::AUTOMATED_FULL,
                'component-a', 'suite-a', 'scenario-a', 'default'),
            new SourceCaseMapping('case-2-partial', CoverageDisposition::AUTOMATED_PARTIAL,
                'component-a', 'suite-a', 'scenario-b', 'default', reason: 'partial',
                coveredAssertions: ['covered'], uncoveredAssertions: ['uncovered']),
            $this->merged('case-3-merged'),
            $this->merged('case-4-merged'),
            new SourceCaseMapping('case-5-executor', CoverageDisposition::EXCLUDED_NO_RELIABLE_EXECUTOR,
                reason: 'no executor', uncoveredAssertions: ['external']),
            new SourceCaseMapping('case-6-human', CoverageDisposition::EXCLUDED_HUMAN_JUDGMENT,
                reason: 'subjective', uncoveredAssertions: ['visual']),
        ];
    }

    private function merged(string $sourceCaseId): SourceCaseMapping
    {
        return new SourceCaseMapping(
            $sourceCaseId,
            CoverageDisposition::MERGED_EQUIVALENT,
            replacementComponentKey: 'component-a',
            replacementSuiteKey: 'suite-a',
            replacementScenarioKey: 'scenario-a',
            replacementVariantKey: 'default',
            reason: 'equivalent',
            coveredAssertions: ['covered'],
            uncoveredAssertions: ['duplicate'],
        );
    }
}
