<?php

namespace App\Contracts;

use Playwright\Page\PageInterface;
use App\Models\Profile;
use App\Models\Test;
use Illuminate\Http\Request;
use Playwright\Browser\BrowserContextInterface;
use Playwright\Browser\BrowserInterface;
use Playwright\Playwright;

class TestContext
{
    public Profile $profile;
    public BrowserContextInterface $browser;
    public PageInterface $page;
    public Test $test;
    public array $cookies = [];

    public function __construct(Profile $profile, Test $test)
    {
        $request = app(Request::class);
        $this->profile = $profile;
        $this->test = $test;
        $config = $profile->data;
        $customize_profile = $request->attributes->get('profile_customize');
        $browserSettings = $config?->browser?->settings ?? ['headless' => false];
        $driver = $customize_profile?->browser?->value ?? $config?->browser?->driver ?? 'chrome';

        $this->browser = match ($driver) {
            'chrome' => Playwright::chromium($browserSettings),
            'firefox' => Playwright::firefox($browserSettings),
            'safari' => Playwright::webkit($browserSettings),
            default => Playwright::chromium($browserSettings),
        };

        $this->page = $this->browser->newPage();
    }
}
