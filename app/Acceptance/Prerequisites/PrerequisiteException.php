<?php

namespace App\Acceptance\Prerequisites;

use RuntimeException;

final class PrerequisiteException extends RuntimeException
{
    private const CODES = [
        'prerequisite_request_not_found',
        'prerequisite_schema_invalid',
        'input_required',
        'approval_required',
        'schema_changed',
        'input_invalid',
        'secret_literal_forbidden',
        'approval_stale',
        'request_expired',
        'invalid_transition',
        'conflict',
        'prerequisite_persistence_failed',
    ];

    private function __construct(public readonly string $errorCode)
    {
        parent::__construct($errorCode);
    }

    public static function because(string $errorCode): self
    {
        if (! in_array($errorCode, self::CODES, true)) {
            $errorCode = 'prerequisite_persistence_failed';
        }

        return new self($errorCode);
    }
}
