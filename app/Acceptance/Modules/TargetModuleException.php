<?php

namespace App\Acceptance\Modules;

use RuntimeException;

/** Safe exception carrying only a stable operation error code. */
final class TargetModuleException extends RuntimeException
{
    private function __construct(public readonly string $errorCode)
    {
        parent::__construct($errorCode);
    }

    public static function because(string $errorCode): self
    {
        return new self($errorCode);
    }
}
