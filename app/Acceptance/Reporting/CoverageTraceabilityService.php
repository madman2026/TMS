<?php

namespace App\Acceptance\Reporting;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Reporting\Data\AcceptanceCoverageView;
use App\Acceptance\Reporting\Data\CoverageCounts;
use App\Acceptance\Reporting\Data\CoverageSourceCaseView;
use App\Contracts\AcceptanceCoverageProvider;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceCatalog;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Throwable;

final class CoverageTraceabilityService
{
    public function __construct(
        private readonly AcceptanceAppRegistry $registry,
        private readonly AcceptanceCatalog $catalog,
        private readonly ConfigRepository $config,
    ) {}

    /** @param list<CoverageDisposition> $dispositions */
    public function report(
        string $appKey,
        ?string $afterSourceCaseId = null,
        ?int $limit = null,
        array $dispositions = [],
    ): AcceptanceCoverageView {
        [$default, $maximum, $maxSourceCases] = $this->limits();
        $limit ??= $default;
        if (! $this->validKey($appKey) || $limit < 1 || $limit > $maximum
            || ($afterSourceCaseId !== null && ! $this->validSourceId($afterSourceCaseId))
            || count(array_filter($dispositions, fn (mixed $item): bool => $item instanceof CoverageDisposition)) !== count($dispositions)) {
            throw AcceptanceReportingException::because('acceptance_report_query_invalid');
        }

        return $this->build($appKey, $afterSourceCaseId, $limit, $dispositions, $maxSourceCases);
    }

    public function export(string $appKey): AcceptanceCoverageView
    {
        [, , $maxSourceCases] = $this->limits();
        $maximum = $this->configInt('max_export_rows');
        if (! $this->validKey($appKey)) {
            throw AcceptanceReportingException::because('acceptance_report_query_invalid');
        }
        if ($maximum < 1 || $maximum > 25_000) {
            throw AcceptanceReportingException::because('acceptance_configuration_invalid');
        }

        return $this->build($appKey, null, $maximum, [], $maxSourceCases, true);
    }

    /** @param list<CoverageDisposition> $dispositions */
    private function build(
        string $appKey,
        ?string $afterSourceCaseId,
        int $limit,
        array $dispositions,
        int $maxSourceCases,
        bool $export = false,
    ): AcceptanceCoverageView {
        $provider = $this->registry->app($appKey);
        if (! $provider instanceof AcceptanceCoverageProvider) {
            throw AcceptanceReportingException::because('acceptance_coverage_mapping_invalid');
        }

        try {
            $catalogVersion = $this->catalog->version($appKey);
            $catalogIdentities = [];
            foreach ($this->catalog->descriptors($appKey) as $descriptor) {
                foreach ($this->catalog->variants($appKey, $descriptor->key) as $variant) {
                    $key = $this->identityKey([
                        $descriptor->componentKey,
                        $descriptor->suiteKey,
                        $descriptor->key,
                        $variant->key,
                    ]);
                    $catalogIdentities[$key] = true;
                    if (count($catalogIdentities) > $maxSourceCases) {
                        throw AcceptanceReportingException::because('acceptance_export_limit_exceeded');
                    }
                }
            }

            $mappings = [];
            foreach ($provider->sourceCaseMappings() as $mapping) {
                if (! $mapping instanceof SourceCaseMapping) {
                    throw AcceptanceReportingException::because('acceptance_coverage_mapping_invalid');
                }
                $mappings[] = $mapping;
                if (count($mappings) > $maxSourceCases) {
                    throw AcceptanceReportingException::because('acceptance_export_limit_exceeded');
                }
            }
        } catch (AcceptanceReportingException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw AcceptanceReportingException::because('acceptance_coverage_mapping_invalid');
        }

        usort($mappings, fn (SourceCaseMapping $left, SourceCaseMapping $right): int => strcmp(
            $left->sourceCaseId,
            $right->sourceCaseId,
        ));
        $sources = [];
        $direct = [];
        $counts = array_fill_keys(array_column(CoverageDisposition::cases(), 'value'), 0);
        foreach ($mappings as $mapping) {
            if (isset($sources[$mapping->sourceCaseId])) {
                throw AcceptanceReportingException::because('acceptance_coverage_mapping_invalid');
            }
            $sources[$mapping->sourceCaseId] = true;
            $counts[$mapping->disposition->value]++;
            $identity = $mapping->executableIdentity();
            if ($identity === null) {
                continue;
            }
            $key = $this->identityKey($identity);
            if (isset($direct[$key])) {
                throw AcceptanceReportingException::because('acceptance_coverage_mapping_invalid');
            }
            if (! isset($catalogIdentities[$key])) {
                throw AcceptanceReportingException::because('acceptance_coverage_incomplete');
            }
            $direct[$key] = true;
        }
        foreach ($mappings as $mapping) {
            $replacement = $mapping->replacementIdentity();
            if ($replacement !== null && ! isset($direct[$this->identityKey($replacement)])) {
                throw AcceptanceReportingException::because('acceptance_coverage_incomplete');
            }
        }
        if (array_diff_key($catalogIdentities, $direct) !== []) {
            throw AcceptanceReportingException::because('acceptance_coverage_incomplete');
        }
        if ($afterSourceCaseId !== null && ! isset($sources[$afterSourceCaseId])) {
            throw AcceptanceReportingException::because('acceptance_report_query_invalid');
        }
        if ($export && count($mappings) > $limit) {
            throw AcceptanceReportingException::because('acceptance_export_limit_exceeded');
        }

        $allowed = array_fill_keys(array_map(fn (CoverageDisposition $item): string => $item->value, $dispositions), true);
        $filtered = array_values(array_filter(
            $mappings,
            fn (SourceCaseMapping $mapping): bool => ($afterSourceCaseId === null
                    || strcmp($mapping->sourceCaseId, $afterSourceCaseId) > 0)
                && ($allowed === [] || isset($allowed[$mapping->disposition->value])),
        ));
        $hasMore = count($filtered) > $limit;
        $page = array_slice($filtered, 0, $limit);
        $views = array_map(fn (SourceCaseMapping $mapping): CoverageSourceCaseView => CoverageSourceCaseView::fromMapping($mapping), $page);
        $last = $page === [] ? null : $page[array_key_last($page)]->sourceCaseId;

        return new AcceptanceCoverageView(
            $appKey,
            $catalogVersion,
            new CoverageCounts(
                $counts[CoverageDisposition::AUTOMATED_FULL->value],
                $counts[CoverageDisposition::AUTOMATED_PARTIAL->value],
                $counts[CoverageDisposition::MERGED_EQUIVALENT->value],
                $counts[CoverageDisposition::EXCLUDED_NO_RELIABLE_EXECUTOR->value],
                $counts[CoverageDisposition::EXCLUDED_HUMAN_JUDGMENT->value],
            ),
            $views,
            $limit,
            $afterSourceCaseId,
            $hasMore ? $last : null,
            $hasMore,
        );
    }

    /** @return array{int, int, int} */
    private function limits(): array
    {
        $default = $this->configInt('default_page_size');
        $maximum = $this->configInt('max_page_size');
        $maxSourceCases = $this->configInt('max_source_cases');
        if ($default < 1 || $default > $maximum || $maximum > 1000
            || $maxSourceCases < 24_668 || $maxSourceCases > 25_000) {
            throw AcceptanceReportingException::because('acceptance_configuration_invalid');
        }

        return [$default, $maximum, $maxSourceCases];
    }

    private function configInt(string $key): int
    {
        $value = $this->config->get('acceptance.reporting.'.$key);

        return is_int($value) ? $value : 0;
    }

    /** @param array{string, string, string, string} $identity */
    private function identityKey(array $identity): string
    {
        return implode("\0", $identity);
    }

    private function validKey(string $value): bool
    {
        return strlen($value) <= 64 && preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $value) === 1;
    }

    private function validSourceId(string $value): bool
    {
        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]{0,127}$/D', $value) === 1;
    }
}
