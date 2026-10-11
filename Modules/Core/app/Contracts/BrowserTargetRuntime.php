<?php

namespace Modules\Core\Contracts;

use Playwright\Browser\BrowserContextInterface;
use Playwright\Page\PageInterface;

interface BrowserTargetRuntime
{
    public function prepare(BrowserContextInterface $browser): void;

    public function open(PageInterface $page, string $surfaceKey): void;
}
