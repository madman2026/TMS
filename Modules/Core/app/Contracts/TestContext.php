<?php

namespace Modules\Core\Contracts;

use Playwright\Browser\BrowserContextInterface;
use Playwright\Page\PageInterface;

class TestContext
{
    public readonly PageInterface $page;

    private bool $closed = false;

    public function __construct(
        public readonly BrowserContextInterface $browser,
        int $timeoutMs,
    ) {
        $this->browser->setDefaultTimeout($timeoutMs);
        $this->browser->setDefaultNavigationTimeout($timeoutMs);
        $this->page = $this->browser->newPage();
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
