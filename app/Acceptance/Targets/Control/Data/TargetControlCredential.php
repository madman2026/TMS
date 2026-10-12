<?php

namespace App\Acceptance\Targets\Control\Data;

use Closure;
use InvalidArgumentException;
use LogicException;
use SensitiveParameter;

/** A short-lived credential that can only be consumed inside one callback. */
final readonly class TargetControlCredential
{
    public function __construct(#[SensitiveParameter] private string $value)
    {
        if ($value === '' || strlen($value) > 4096 || preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            throw new InvalidArgumentException('target_control_credential_invalid');
        }
    }

    public function withValue(#[SensitiveParameter] Closure $consumer): mixed
    {
        return $consumer($this->value);
    }

    public function __toString(): string
    {
        throw new LogicException('target_control_credential_non_stringable');
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new LogicException('target_control_credential_non_serializable');
    }

    /** @param array<never, never> $data */
    public function __unserialize(array $data): void
    {
        throw new LogicException('target_control_credential_non_serializable');
    }

    /** @return array<never, never> */
    public function __debugInfo(): array
    {
        return [];
    }
}
