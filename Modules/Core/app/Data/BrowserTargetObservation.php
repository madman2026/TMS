<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use Modules\Core\Exceptions\AcceptanceExecutionException;

/**
 * Bounded target-owned semantic facts. Raw target content must be omitted
 * before values cross this contract.
 */
final readonly class BrowserTargetObservation
{
    /** @var array<string, bool|int|float|string|null> */
    public array $facts;

    /** @param array<string, mixed> $facts */
    public function __construct(public string $key, array $facts)
    {
        if (! self::validKey($key) || count($facts) < 1 || count($facts) > 16 || array_is_list($facts)) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        $safe = [];
        foreach ($facts as $factKey => $value) {
            if (! is_string($factKey) || ! self::validKey($factKey) || ! self::validValue($value)) {
                throw AcceptanceExecutionException::configurationInvalid();
            }

            // Copy only validated scalars so caller-held references cannot mutate the observation.
            $safe[$factKey] = $value;
        }

        $this->facts = $safe;
    }

    private static function validKey(string $key): bool
    {
        return strlen($key) <= 64
            && preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) === 1;
    }

    private static function validValue(mixed $value): bool
    {
        if (is_bool($value) || $value === null) {
            return true;
        }

        if (is_int($value) || is_float($value)) {
            return is_finite((float) $value)
                && $value >= -1_000_000_000
                && $value <= 1_000_000_000;
        }

        return is_string($value) && self::validKey($value);
    }
}
