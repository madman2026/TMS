<?php

namespace App\Contracts;

use App\BrowserTypeEnum;
use App\InternetSpeedEnum;
use Playwright\Browser\Browser;

class RequestOptions
{
    public function __construct(
        public readonly BrowserTypeEnum $browser,
        public readonly InternetSpeedEnum $speed,
    ){}
}
