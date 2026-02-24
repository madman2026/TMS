<?php

namespace Modules\Core\Contracts;

use App\DeviceTypeEnum;
use InvalidArgumentException;

class Device
{
    public static function androidMobile(): array
    {
        return [
            'viewport' => ['width' => 412, 'height' => 915],
            'userAgent' => 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Mobile Safari/537.36',
            'deviceScaleFactor' => 2.625,
            'isMobile' => true,
            'hasTouch' => true,
        ];
    }

    public static function androidTablet(): array
    {
        return [
            'viewport' => ['width' => 800, 'height' => 1280],
            'userAgent' => 'Mozilla/5.0 (Linux; Android 13; SM-X700) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
            'deviceScaleFactor' => 2,
            'isMobile' => true,
            'hasTouch' => true,
        ];
    }

    public static function iosMobile(): array
    {
        return [
            'viewport' => ['width' => 390, 'height' => 844],
            'userAgent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
            'deviceScaleFactor' => 3,
            'isMobile' => true,
            'hasTouch' => true,
        ];
    }

    public static function iosTablet(): array
    {
        return [
            'viewport' => ['width' => 820, 'height' => 1180],
            'userAgent' => 'Mozilla/5.0 (iPad; CPU OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Safari/604.1',
            'deviceScaleFactor' => 2,
            'isMobile' => true,
            'hasTouch' => true,
        ];
    }

    public static function laptop(): array
    {
        return [
            'viewport' => ['width' => 1366, 'height' => 768],
            'userAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36',
            'deviceScaleFactor' => 1,
            'isMobile' => false,
            'hasTouch' => false,
        ];
    }

    public static function desktop(): array
    {
        return [
            'viewport' => ['width' => 1920, 'height' => 1080],
            'userAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36',
            'deviceScaleFactor' => 1,
            'isMobile' => false,
            'hasTouch' => false,
        ];
    }

    // گرفتن داینامیک بر اساس نام
    public static function get(DeviceTypeEnum $name): array
    {
        return match ($name) {
            'android-mobile' => self::androidMobile(),
            'android-tablet' => self::androidTablet(),
            'ios-mobile' => self::iosMobile(),
            'ios-tablet' => self::iosTablet(),
            'laptop' => self::laptop(),
            'desktop' => self::desktop(),
            default => throw new InvalidArgumentException("Unknown device: {$name}")
        };
    }

    // لیست نام‌ها برای حلقه تست
    public static function all(): array
    {
        return [
            'android-mobile',
            'android-tablet',
            'ios-mobile',
            'ios-tablet',
            'laptop',
            'desktop',
        ];
    }
}
