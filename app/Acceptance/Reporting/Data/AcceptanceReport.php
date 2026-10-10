<?php

namespace App\Acceptance\Reporting\Data;

use InvalidArgumentException;

final readonly class AcceptanceReport
{
    /** @var list<AcceptanceReportItem> */
    public array $items;

    /** @param list<AcceptanceReportItem> $items */
    public function __construct(
        public AcceptanceStatusView $status,
        array $items,
        public int $limit,
        public ?int $afterItemId,
        public ?int $nextItemId,
        public bool $hasMore,
        public int $version = 1,
    ) {
        if ($version !== 1 || $limit < 1 || ($afterItemId !== null && $afterItemId < 1)
            || ($nextItemId !== null && $nextItemId < 1)
            || $hasMore !== ($nextItemId !== null) || count($items) > $limit
            || count(array_filter($items, fn (mixed $item): bool => $item instanceof AcceptanceReportItem)) !== count($items)) {
            throw new InvalidArgumentException('acceptance_report_invalid');
        }
        $ids = array_map(fn (AcceptanceReportItem $item): int => $item->id, $items);
        $sorted = $ids;
        sort($sorted, SORT_NUMERIC);
        if ($ids !== $sorted || count(array_unique($ids, SORT_NUMERIC)) !== count($ids)
            || ($afterItemId !== null && $ids !== [] && min($ids) <= $afterItemId)
            || ($hasMore && ($ids === [] || $nextItemId !== $ids[array_key_last($ids)]))) {
            throw new InvalidArgumentException('acceptance_report_invalid');
        }
        $this->items = array_values($items);
    }
}
