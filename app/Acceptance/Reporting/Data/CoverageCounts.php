<?php

namespace App\Acceptance\Reporting\Data;

use InvalidArgumentException;

final readonly class CoverageCounts
{
    public int $total;

    public function __construct(
        public int $automatedFull,
        public int $automatedPartial,
        public int $mergedEquivalent,
        public int $excludedNoReliableExecutor,
        public int $excludedHumanJudgment,
    ) {
        $counts = [$automatedFull, $automatedPartial, $mergedEquivalent,
            $excludedNoReliableExecutor, $excludedHumanJudgment];
        if (min($counts) < 0) {
            throw new InvalidArgumentException('coverage_counts_invalid');
        }
        $this->total = array_sum($counts);
    }
}
