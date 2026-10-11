<?php

namespace Tests\Support;

use InvalidArgumentException;

final class BrowserTestConfiguration
{
    public const HEADLESS_ENV = 'ACCEPTANCE_HEADLESS';

    public static function headless(): bool
    {
        $value = $_ENV[self::HEADLESS_ENV]
            ?? $_SERVER[self::HEADLESS_ENV]
            ?? getenv(self::HEADLESS_ENV);

        if ($value === false) {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        return match (strtolower(trim((string) $value))) {
            '1', 'true', '(true)', 'yes', 'on' => true,
            '', '0', 'false', '(false)', 'no', 'off' => false,
            default => throw new InvalidArgumentException(
                self::HEADLESS_ENV.' must be a boolean value.',
            ),
        };
    }
}
