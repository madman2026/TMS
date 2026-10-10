<?php

namespace App\Acceptance\Reporting\Data;

use App\Acceptance\Execution\Enums\AttemptState;
use App\Acceptance\Operations\Data\OperationResult;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class AcceptanceAttemptView
{
    /** @var list<AcceptanceEvidenceView> */
    public array $evidence;

    /** @param list<AcceptanceEvidenceView> $evidence */
    public function __construct(
        public int $id,
        public int $attemptNumber,
        public AttemptState $state,
        public ?int $testId,
        public string $executorCapability,
        public ?string $executorKey,
        public int $infrastructureAttempts,
        public bool $executorEntered,
        public ?string $errorCode,
        public ?string $cleanupErrorCode,
        public ?bool $retryable,
        public ?bool $permanent,
        public bool $adminActionRequired,
        public ?DateTimeImmutable $queuedAt,
        public ?DateTimeImmutable $startedAt,
        public ?DateTimeImmutable $finishedAt,
        array $evidence = [],
        public int $version = 1,
    ) {
        if ($version !== 1 || $id < 1 || $attemptNumber < 1 || $infrastructureAttempts < 0
            || ($testId !== null && $testId < 1)
            || ! $this->validKey($executorCapability)
            || ($executorKey !== null && ! $this->validKey($executorKey))
            || ($errorCode !== null && ! in_array($errorCode, OperationResult::ERROR_CODES, true))
            || ($cleanupErrorCode !== null && ! in_array($cleanupErrorCode, OperationResult::ERROR_CODES, true))
            || ! $this->timestampsOrdered($queuedAt, $startedAt, $finishedAt)
            || count(array_filter($evidence, fn (mixed $item): bool => $item instanceof AcceptanceEvidenceView)) !== count($evidence)) {
            throw new InvalidArgumentException('acceptance_attempt_view_invalid');
        }
        $ids = array_map(fn (AcceptanceEvidenceView $item): int => $item->id, $evidence);
        if ($ids !== array_values(array_unique($ids)) || $ids !== $this->sorted($ids)) {
            throw new InvalidArgumentException('acceptance_attempt_view_invalid');
        }
        $this->evidence = array_values($evidence);
    }

    private function validKey(string $value): bool
    {
        return strlen($value) <= 64 && preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $value) === 1;
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

    /** @param list<int> $values @return list<int> */
    private function sorted(array $values): array
    {
        sort($values, SORT_NUMERIC);

        return $values;
    }
}
