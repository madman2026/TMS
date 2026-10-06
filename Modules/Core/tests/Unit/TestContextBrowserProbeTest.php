<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use Error;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Modules\Core\Contracts\BrowserProbe;
use Modules\Core\Contracts\TestContext;
use PHPUnit\Framework\TestCase;
use Playwright\Browser\BrowserContextInterface;
use Playwright\Page\PageInterface;

class TestContextBrowserProbeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_context_creates_one_probe_for_its_page(): void
    {
        $page = Mockery::mock(PageInterface::class);
        $browser = $this->browser($page);
        $page->shouldReceive('isClosed')->once()->andReturn(false);
        $page->shouldReceive('evaluate')->once()->withArgs(fn (string $expression): bool =>
            str_contains($expression, 'window.innerWidth') && ! str_contains($expression, 'textContent'))
            ->andReturn(['width' => 800, 'height' => 600, 'deviceScaleFactor' => 1]);
        $context = new TestContext($browser, 30000);

        $this->assertSame($page, $context->page);
        $this->assertInstanceOf(BrowserProbe::class, $context->browserProbe);
        $this->assertSame($context->browserProbe, $context->browserProbe);
        $this->assertSame(['width' => 800, 'height' => 600, 'deviceScaleFactor' => 1], $context->browserProbe->viewport()->requireAvailable());
        $this->assertFalse($context->isClosed());

        $this->expectException(Error::class);
        $context->browserProbe = Mockery::mock(BrowserProbe::class);
    }

    public function test_context_cleanup_remains_idempotent(): void
    {
        $page = Mockery::mock(PageInterface::class);
        $browser = $this->browser($page);
        $browser->shouldReceive('close')->once();
        $page->shouldReceive('isClosed')->once()->andReturn(true);
        $context = new TestContext($browser, 30000);

        $context->close();
        $context->close();

        $this->assertTrue($context->isClosed());
        $this->assertSame('browser_probe_page_closed', $context->browserProbe->viewport()->errorCode);
    }

    private function browser(PageInterface $page): BrowserContextInterface
    {
        $browser = Mockery::mock(BrowserContextInterface::class);
        $browser->shouldReceive('setDefaultTimeout')->once()->with(30000);
        $browser->shouldReceive('setDefaultNavigationTimeout')->once()->with(30000);
        $browser->shouldReceive('newPage')->once()->andReturn($page);

        return $browser;
    }
}
