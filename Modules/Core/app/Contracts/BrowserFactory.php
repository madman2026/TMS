<?php

namespace Modules\Core\Contracts;

use Playwright\Browser\BrowserContextInterface;
use Playwright\Playwright;

class BrowserFactory
{
    public static function make(string $driver, array $launchOptions): BrowserContextInterface
    {
        return match ($driver) {
            'chrome' => Playwright::chromium($launchOptions),
            'firefox' => Playwright::firefox($launchOptions),
            'safari' => Playwright::webkit($launchOptions),
            default => Playwright::chromium($launchOptions),
        };
    }
}
