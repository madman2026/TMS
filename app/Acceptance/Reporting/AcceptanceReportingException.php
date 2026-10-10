<?php

namespace App\Acceptance\Reporting;

use RuntimeException;

final class AcceptanceReportingException extends RuntimeException
{
    public function __construct(public readonly string $errorCode)
    {
        parent::__construct($errorCode);
    }

    public static function because(string $errorCode): self
    {
        return new self($errorCode);
    }
}
