<?php

namespace App;

use Modules\Core\Contracts\Device;

enum DeviceTypeEnum: string
{
    case ANDROID_MOBILE='android-mobile';
    case ANDROID_TABLET='android-tablet';
    case IOS_MOBILE='ios-mobile';
    case IOS_TABLET='ios-tablet';
    case LAPTOP= 'laptop';
    case DESKTOP='desktop';

    public function settings(): array
    {
        return match ($this) {
            self::ANDROID_MOBILE => Device::androidMobile(),
            self::ANDROID_TABLET => Device::androidTablet(),
            self::IOS_MOBILE => Device::iosMobile(),
            self::IOS_TABLET => Device::iosTablet(),
            self::LAPTOP => Device::laptop(),
            self::DESKTOP => Device::desktop(),
        };
    }
}
