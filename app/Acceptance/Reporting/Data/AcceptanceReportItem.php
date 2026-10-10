<?php

namespace App\Acceptance\Reporting\Data;

use App\Acceptance\Execution\Enums\BatchItemState;
use App\Acceptance\Operations\Data\OperationResult;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class AcceptanceReportItem
{
    /** @var list<AcceptanceAttemptView> */
    public array $attempts;

    /** @param list<AcceptanceAttemptView> $attempts */
    public function __construct(
        public int $id,
        public int $ordinal,
        public string $appKey,
        public string $componentKey,
        public string $suiteKey,
        public string $scenarioKey,
        public string $variantKey,
        public string $capability,
        public string $catalogVersion,
        public BatchItemState $state,
        public int $attemptCount,
        public ?string $errorCode,
        public ?string $cleanupErrorCode,
        public ?bool $retryable,
        public ?bool $permanent,
        public bool $adminActionRequired,
        public ?DateTimeImmutable $queuedAt,
        public ?DateTimeImmutable $startedAt,
        public ?DateTimeImmutable $finishedAt,
        array $attempts = [],
        public int $version = 1,
    ) {
        if ($version !== 1 || min([$id, $ordinal, $attemptCount]) < 0 || $id < 1
            || $attemptCount !== count($attempts)
            || ($errorCode !== null && ! in_array($errorCode, OperationResult::ERROR_CODES, true))
            || ($cleanupErrorCode !== null && ! in_array($cleanupErrorCode, OperationResult::ERROR_CODES, true))
            || ! $this->timestampsOrdered($queuedAt, $startedAt, $finishedAt)
            || count(array_filter($attempts, fn (mixed $item): bool => $item instanceof AcceptanceAttemptView)) !== count($attempts)) {
            throw new InvalidArgumentException('acceptance_report_item_invalid');
        }
        foreach ([$appKey, $componentKey, $suiteKey, $scenarioKey, $variantKey, $capability, $catalogVersion] as $key) {
            if (strlen($key) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1) {
                throw new InvalidArgumentException('acceptance_report_item_invalid');
            }
        }
        $order = array_map(
            fn (AcceptanceAttemptView $attempt): string => str_pad((string) $attempt->attemptNumber, 10, '0', STR_PAD_LEFT)
                .':'.str_pad((string) $attempt->id, 20, '0', STR_PAD_LEFT),
            $attempts,
        );
        $sorted = $order;
        sort($sorted, SORT_STRING);
        if ($order !== $sorted || count(array_unique($order, SORT_STRING)) !== count($order)) {
            throw new InvalidArgumentException('acceptance_report_item_invalid');
        }
        $this->attempts = array_values($attempts);
    }

    private function timestampsOrdered(
        ?DateTimeImmutable $queuedAt,
        ?DateTimeImmutable $startedAt,
        ?DateTimeImmutable $finishedAt,
    ): bool {
        return ($queuedAt === null || $startedAt === null || $queuedAt <= $startedAt)
            && ($startedAt === null || $finishedAt === null || $startedAt <= $finishedAt)
            && ($queuedAt === null || $finishedAt === null || $queuedAt <= $finishedAt);
    }
}
