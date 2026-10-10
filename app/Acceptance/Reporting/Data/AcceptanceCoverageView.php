<?php

namespace App\Acceptance\Reporting\Data;

use InvalidArgumentException;

final readonly class AcceptanceCoverageView
{
    /** @var list<CoverageSourceCaseView> */
    public array $cases;

    /** @param list<CoverageSourceCaseView> $cases */
    public function __construct(
        public string $appKey,
        public string $catalogVersion,
        public CoverageCounts $counts,
        array $cases,
        public int $limit,
        public ?string $afterSourceCaseId,
        public ?string $nextSourceCaseId,
        public bool $hasMore,
        public int $version = 1,
    ) {
        if ($version !== 1 || $limit < 1 || count($cases) > $limit
            || ! $this->validKey($appKey) || ! $this->validKey($catalogVersion)
            || ($afterSourceCaseId !== null && ! $this->validSourceId($afterSourceCaseId))
            || ($nextSourceCaseId !== null && ! $this->validSourceId($nextSourceCaseId))
            || $hasMore !== ($nextSourceCaseId !== null)
            || count(array_filter($cases, fn (mixed $item): bool => $item instanceof CoverageSourceCaseView)) !== count($cases)) {
            throw new InvalidArgumentException('acceptance_coverage_view_invalid');
        }
        $ids = array_map(fn (CoverageSourceCaseView $case): string => $case->sourceCaseId, $cases);
        $sorted = $ids;
        sort($sorted, SORT_STRING);
        if ($ids !== $sorted || count(array_unique($ids, SORT_STRING)) !== count($ids)
            || ($afterSourceCaseId !== null && $ids !== [] && strcmp($ids[0], $afterSourceCaseId) <= 0)
            || ($hasMore && ($ids === [] || $nextSourceCaseId !== $ids[array_key_last($ids)]))) {
            throw new InvalidArgumentException('acceptance_coverage_view_invalid');
        }
        $this->cases = array_values($cases);
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
