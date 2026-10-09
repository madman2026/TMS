<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use Modules\Core\Exceptions\ExecutorException;

final readonly class HttpExecutorData
{
    /** @var array<string, bool|float|int|string|null> */
    public array $observations;

    /** @param array<string, bool|float|int|string|null> $observations */
    public function __construct(public int $statusCode, array $observations, public int $version = 1)
    {
        if ($version !== 1 || $statusCode < 100 || $statusCode > 599 || count($observations) > 64) {
            throw new ExecutorException('unsafe_executor_result');
        }

        $copy = [];
        foreach ($observations as $key => $value) {
            if (! is_string($key) || strlen($key) > 64
                || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1
                || ! self::safeValue($value)) {
                throw new ExecutorException('unsafe_executor_result');
            }

            $copy[$key] = $value;
        }

        $this->observations = $copy;
    }

    private static function safeValue(mixed $value): bool
    {
        if ($value === null || is_bool($value) || is_int($value)) {
            return true;
        }

        if (is_float($value)) {
            return is_finite($value);
        }

        return is_string($value)
            && strlen($value) <= 1024
            && preg_match('//u', $value) === 1
            && preg_match('/[\p{C}]/u', $value) !== 1;
    }
}
