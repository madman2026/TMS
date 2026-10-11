<?php

namespace Modules\Core\Contracts;

use InvalidArgumentException;
use Modules\Core\Data\AcceptanceExecutionIdentity;
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
        public readonly AcceptanceExecutionIdentity $identity,
        private readonly BrowserTargetRuntime $targetRuntime,
        int $timeoutMs,
    ) {
        $this->browser->setDefaultTimeout($timeoutMs);
        $this->browser->setDefaultNavigationTimeout($timeoutMs);
        $this->targetRuntime->prepare($this->browser);
        $this->page = $this->browser->newPage();
        $this->browserProbe = new PlaywrightBrowserProbe($this->page, $timeoutMs);
    }

    public function openTargetSurface(string $surfaceKey): void
    {
        if (strlen($surfaceKey) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $surfaceKey) !== 1) {
            throw new InvalidArgumentException('acceptance_configuration_invalid');
        }

        $this->targetRuntime->open($this->page, $surfaceKey);
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
