<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use Modules\Core\Exceptions\ExecutorException;

final readonly class ExecutorTraceContext
{
    public function __construct(
        public ?string $correlationId = null,
        public ?string $operationId = null,
        public ?int $batchId = null,
        public ?int $itemId = null,
        public ?int $attemptId = null,
        public ?int $testId = null,
        public int $version = 1,
    ) {
        if ($version !== 1
            || ($correlationId !== null && ! self::isUuid($correlationId))
            || ($operationId !== null && ! self::isUuid($operationId))) {
            throw new ExecutorException('executor_request_invalid');
        }

        foreach ([$batchId, $itemId, $attemptId, $testId] as $id) {
            if ($id !== null && $id < 1) {
                throw new ExecutorException('executor_request_invalid');
            }
        }
    }

    /** @return array<string, int|string> */
    public function logContext(): array
    {
        return array_filter([
            'correlation_id' => $this->correlationId,
            'operation_id' => $this->operationId,
            'batch_id' => $this->batchId,
            'item_id' => $this->itemId,
            'attempt_id' => $this->attemptId,
            'test_id' => $this->testId,
        ], static fn (int|string|null $value): bool => $value !== null);
    }

    private static function isUuid(string $value): bool
    {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D', $value) === 1;
    }
}
