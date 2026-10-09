<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use Modules\Core\Exceptions\ExecutorException;

final readonly class BrowserStepData
{
    public function __construct(
        public int $position,
        public string $name,
        public bool $passed,
        public ?string $errorCode,
        public int $durationMs,
        public bool $critical,
    ) {
        if ($position < 1 || $durationMs < 0 || $durationMs > 86_400_000
            || ! self::safeText($name, 128)
            || ($errorCode !== null && ! self::safeCode($errorCode))
            || ($passed && $errorCode !== null)) {
            throw new ExecutorException('unsafe_executor_result');
        }
    }

    private static function safeCode(string $code): bool
    {
        return strlen($code) <= 64
            && preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $code) === 1;
    }

    private static function safeText(string $value, int $maximumBytes): bool
    {
        return $value !== ''
            && strlen($value) <= $maximumBytes
            && preg_match('//u', $value) === 1
            && preg_match('/[\p{C}]/u', $value) !== 1;
    }
}
