<?php

namespace App\Contracts;

use App\DriverTypeEnum;
use App\InternetSpeedEnum;
use App\DeviceTypeEnum;

class RequestOptions
{
    public function __construct(
        public readonly DriverTypeEnum $browser,
        public readonly DeviceTypeEnum $device,
        public readonly InternetSpeedEnum $speed,
    ){}
}
