<?php

namespace Modules\Core\Contracts;

use App\DeviceTypeEnum;
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
    public DeviceTypeEnum $device;
    public array $cookies = [];

    public function __construct(Profile $profile, Test $test)
    {
        $request = app(Request::class);
        $this->profile = $profile;
        $this->test = $test;
        $config = $profile->data;
        $customize_profile = $request->attributes->get('profile_customize');
        $driver = $customize_profile?->browser?->value ?? $config?->browser?->driver ?? 'chrome';
        $this->device = $customize_profile?->device ?? $profile->device ?? DeviceTypeEnum::DESKTOP;
        $contextOptions = array_merge(
            ['headless' => false],
            $this->device->settings(),
            $config?->browser?->settings ?? []
        );
        $this->browser = BrowserFactory::make($driver, $contextOptions);
        $this->page = $this->browser->newPage();
    }
}
