<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use Error;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Modules\Core\Contracts\BrowserProbe;
use Modules\Core\Contracts\BrowserTargetRuntime;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\AcceptanceExecutionIdentity;
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
        $page->shouldReceive('evaluate')->once()->withArgs(fn (string $expression): bool => str_contains($expression, 'window.innerWidth') && ! str_contains($expression, 'textContent'))
            ->andReturn(['width' => 800, 'height' => 600, 'deviceScaleFactor' => 1]);
        $identity = $this->identity();
        $context = new TestContext($browser, $identity, $this->runtime($browser), 30000);

        $this->assertSame($page, $context->page);
        $this->assertSame($identity, $context->identity);
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
        $context = new TestContext($browser, $this->identity(), $this->runtime($browser), 30000);

        $context->close();
        $context->close();

        $this->assertTrue($context->isClosed());
        $this->assertSame('browser_probe_page_closed', $context->browserProbe->viewport()->errorCode);
    }

    public function test_runtime_preparation_finishes_before_page_creation(): void
    {
        $events = [];
        $page = Mockery::mock(PageInterface::class);
        $browser = Mockery::mock(BrowserContextInterface::class);
        $browser->shouldReceive('setDefaultTimeout')->once()->with(30000);
        $browser->shouldReceive('setDefaultNavigationTimeout')->once()->with(30000);
        $browser->shouldReceive('newPage')->once()->andReturnUsing(function () use (&$events, $page): PageInterface {
            $events[] = 'page';

            return $page;
        });
        $runtime = Mockery::mock(BrowserTargetRuntime::class);
        $runtime->shouldReceive('prepare')->once()->with($browser)->andReturnUsing(function () use (&$events): void {
            $events[] = 'prepare';
        });

        new TestContext($browser, $this->identity(), $runtime, 30000);

        $this->assertSame(['prepare', 'page'], $events);
    }

    public function test_context_opens_only_a_valid_semantic_target_surface(): void
    {
        $page = Mockery::mock(PageInterface::class);
        $browser = $this->browser($page);
        $runtime = $this->runtime($browser);
        $runtime->shouldReceive('open')->once()->with($page, 'operations-health');
        $context = new TestContext($browser, $this->identity(), $runtime, 30000);

        $context->openTargetSurface('operations-health');

        $this->expectException(\InvalidArgumentException::class);
        $context->openTargetSurface('https://unsafe.example');
    }

    private function browser(PageInterface $page): BrowserContextInterface
    {
        $browser = Mockery::mock(BrowserContextInterface::class);
        $browser->shouldReceive('setDefaultTimeout')->once()->with(30000);
        $browser->shouldReceive('setDefaultNavigationTimeout')->once()->with(30000);
        $browser->shouldReceive('newPage')->once()->andReturn($page);

        return $browser;
    }

    private function runtime(BrowserContextInterface $browser): BrowserTargetRuntime
    {
        $runtime = Mockery::mock(BrowserTargetRuntime::class);
        $runtime->shouldReceive('prepare')->once()->with($browser);

        return $runtime;
    }

    private function identity(): AcceptanceExecutionIdentity
    {
        return new AcceptanceExecutionIdentity('app', 'component', 'suite', 'scenario', 'variant');
    }
}
