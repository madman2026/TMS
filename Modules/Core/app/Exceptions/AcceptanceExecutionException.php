<?php

namespace Modules\Core\Exceptions;

use RuntimeException;
use Throwable;

final class AcceptanceExecutionException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        public readonly bool $retryable,
        string $safeMessage,
        ?Throwable $previous = null,
    ) {
        parent::__construct($safeMessage, 0, $previous);
    }

    public static function configurationInvalid(?Throwable $previous = null): self
    {
        return new self(
            errorCode: 'acceptance_configuration_invalid',
            retryable: false,
            safeMessage: 'Acceptance run configuration is invalid.',
            previous: $previous,
        );
    }

    public static function browserStartFailed(Throwable $previous): self
    {
        return new self(
            errorCode: 'acceptance_browser_start_failed',
            retryable: true,
            safeMessage: 'The browser could not be started.',
            previous: $previous,
        );
    }

    public static function scenarioFailed(Throwable $previous): self
    {
        return new self(
            errorCode: 'acceptance_scenario_failed',
            retryable: false,
            safeMessage: 'The acceptance scenario could not be completed.',
            previous: $previous,
        );
    }

    public static function persistenceFailed(Throwable $previous): self
    {
        return new self(
            errorCode: 'acceptance_result_persistence_failed',
            retryable: true,
            safeMessage: 'The acceptance result could not be persisted.',
            previous: $previous,
        );
    }
}
