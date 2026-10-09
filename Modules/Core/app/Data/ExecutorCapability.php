<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use Modules\Core\Exceptions\ExecutorException;

final readonly class ExecutorCapability
{
    public const BROWSER = 'browser';

    public const HTTP = 'http';

    public function __construct(public string $key)
    {
        if (strlen($key) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1) {
            throw new ExecutorException('executor_request_invalid');
        }
    }

    public static function browser(): self
    {
        return new self(self::BROWSER);
    }

    public static function http(): self
    {
        return new self(self::HTTP);
    }

    public function equals(self $other): bool
    {
        return $this->key === $other->key;
    }
}
