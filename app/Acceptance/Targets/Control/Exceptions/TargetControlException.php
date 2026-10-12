<?php

namespace App\Acceptance\Targets\Control\Exceptions;

use App\Acceptance\Targets\Control\TargetControlProtocol;
use InvalidArgumentException;
use RuntimeException;

/** Safe exception carrying only protocol-owned failure metadata. */
final class TargetControlException extends RuntimeException
{
    private function __construct(
        public readonly string $errorCode,
        public readonly bool $retryable,
        public readonly bool $permanent,
        public readonly bool $adminActionRequired,
        public readonly ?int $httpStatus = null,
    ) {
        parent::__construct($errorCode);
    }

    public static function because(string $errorCode, ?int $httpStatus = null): self
    {
        if (! in_array($errorCode, TargetControlProtocol::ERROR_CODES, true)
            || ($httpStatus !== null && ($httpStatus < 100 || $httpStatus > 599))) {
            throw new InvalidArgumentException('target_control_exception_invalid');
        }

        [$retryable, $permanent, $adminActionRequired] = match ($errorCode) {
            'target_control_unavailable', 'target_control_cleanup_failed' => [true, false, true],
            default => [false, true, true],
        };

        return new self($errorCode, $retryable, $permanent, $adminActionRequired, $httpStatus);
    }
}
