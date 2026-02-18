<?php

namespace Modules\Core\Traits;

use Playwright\Playwright;

trait WebDriverFactory
{
    public function createBrowser($headless = true)
    {
        return Playwright::chromium([
            'headless' => $headless
        ]);
    }
}
