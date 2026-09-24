<?php

namespace App\Exceptions;

use RuntimeException;

final class AcceptanceRegistryException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $safeMessage,
    ) {
        parent::__construct($safeMessage);
    }

    public static function invalid(): self
    {
        return new self(
            errorCode: 'acceptance_registry_invalid',
            safeMessage: 'The Acceptance Registry definition is invalid.',
        );
    }

    public static function duplicate(): self
    {
        return new self(
            errorCode: 'acceptance_registry_duplicate',
            safeMessage: 'The Acceptance Registry contains a duplicate key.',
        );
    }
}
