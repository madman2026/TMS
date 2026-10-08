<?php

namespace App\Acceptance\Operations;

use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use Closure;
use InvalidArgumentException;
use LogicException;
use Throwable;
use UnexpectedValueException;

/** Explicit lazy factories keep inspection outside the execution dependency graph. */
final class AcceptanceOperationRegistry
{
    /** @var array<string, Closure> */
    private array $factories = [];

    public function register(string $name, Closure $factory): void
    {
        if (! preg_match('/^[a-z0-9][a-z0-9._-]{0,63}$/D', $name)) {
            throw new InvalidArgumentException('operation_registry_invalid');
        }
        if ($this->has($name)) {
            throw new LogicException('operation_registry_duplicate');
        }
        $this->factories[$name] = $factory;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->factories);
    }

    public function resolve(string $name): ?AcceptanceOperationHandler
    {
        if (! $this->has($name)) {
            return null;
        }
        try {
            $handler = ($this->factories[$name])();
            if ($handler instanceof AcceptanceOperationHandler && $handler->name() === $name) {
                return $handler;
            }
        } catch (Throwable) {
            // Drop arbitrary factory text and exception chains at this boundary.
        }
        throw new UnexpectedValueException('operation_registry_invalid');
    }
}
