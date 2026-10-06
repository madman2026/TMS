<?php

namespace Modules\Core\Contracts;

use Modules\Core\Services\PlaywrightBrowserProbe;
use Playwright\Browser\BrowserContextInterface;
use Playwright\Page\PageInterface;

class TestContext
{
    public readonly PageInterface $page;

    public readonly BrowserProbe $browserProbe;

    private bool $closed = false;

    public function __construct(
        public readonly BrowserContextInterface $browser,
        int $timeoutMs,
    ) {
        $this->browser->setDefaultTimeout($timeoutMs);
        $this->browser->setDefaultNavigationTimeout($timeoutMs);
        $this->page = $this->browser->newPage();
        $this->browserProbe = new PlaywrightBrowserProbe($this->page, $timeoutMs);
    }

    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;
        $this->browser->close();
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }
}
