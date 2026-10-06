<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Feature;

use Closure;
use Modules\Core\Contracts\BrowserProbe;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Enums\BrowserObservationStatus;
use Modules\Core\Exceptions\BrowserProbeException;
use PHPUnit\Framework\TestCase;
use Playwright\Configuration\PlaywrightConfig;
use Playwright\Network\RouteInterface;
use Playwright\Page\PageInterface;
use Playwright\PlaywrightFactory;
use Psr\Log\NullLogger;

/** Real local Chrome evidence, not bundled Chromium or other engine coverage. */
class PlaywrightBrowserProbeSmokeTest extends TestCase
{
    public function test_visibility_geometry_and_clipping_on_local_content(): void
    {
        $this->withPage(function (PageInterface $page, BrowserProbe $probe): void {
            $page->setContent(<<<'HTML'
                <style>
                    body { margin: 0; }
                    #clip { position: absolute; left: 10px; top: 20px; width: 100px; height: 40px; overflow: hidden; }
                    #target { position: absolute; left: 50px; top: 0; width: 100px; height: 40px; }
                    #overflow { position: absolute; top: 100px; width: 20px; height: 20px; overflow: hidden; }
                    #overflow div { width: 60px; height: 60px; }
                    #hidden { display: none; }
                    #transform { position: absolute; top: 200px; width: 30px; height: 30px; transform: rotate(10deg); }
                </style>
                <div id="clip"><div id="target"></div></div>
                <div id="overflow"><div></div></div>
                <div id="hidden"></div><div class="multiple"></div><div class="multiple"></div>
                <div id="transform"></div>
                HTML);
            $target = $page->locator('#target');
            $this->assertSame(['visible' => true, 'enabled' => true], $probe->visibility($target)->requireAvailable());
            $layout = $probe->layout($target)->requireAvailable();
            $this->assertEquals(60, $layout['x']);
            $this->assertEquals(20, $layout['y']);
            $this->assertEquals(100, $layout['width']);
            $this->assertEquals(40, $layout['height']);
            $this->assertEqualsWithDelta(0.5, $layout['visibleFraction'], 0.0001);
            $this->assertTrue($layout['clipped']);
            $this->assertFalse($layout['overflowX']);
            $this->assertFalse($layout['overflowY']);
            $overflow = $probe->layout($page->locator('#overflow'))->requireAvailable();
            $this->assertTrue($overflow['overflowX']);
            $this->assertTrue($overflow['overflowY']);
            $this->assertEquals(1, $overflow['visibleFraction']);

            $hidden = $page->locator('#hidden');
            $this->assertFalse($probe->visibility($hidden)->requireAvailable()['visible']);
            $missingGeometry = $probe->layout($hidden);
            $this->assertSame('browser_probe_geometry_unavailable', $missingGeometry->errorCode);
            $this->assertSame([], $missingGeometry->data);
            $this->assertSame('browser_probe_locator_missing', $probe->visibility($page->locator('#absent'))->errorCode);
            $this->assertSame('browser_probe_locator_ambiguous', $probe->visibility($page->locator('.multiple'))->errorCode);
            $this->assertSame('browser_probe_capability_unsupported', $probe->layout($page->locator('#transform'))->errorCode);

            $this->expectException(BrowserProbeException::class);
            $missingGeometry->requireAvailable();
        });
    }

    public function test_native_focus_tab_keyboard_and_scroll_observe_post_state(): void
    {
        $this->withPage(function (PageInterface $page, BrowserProbe $probe): void {
            $page->setContent(<<<'HTML'
                <style>body { margin: 0; min-height: 2200px; }</style>
                <script>window.localActions = 0;</script>
                <button id="one" onclick="window.localActions++">First synthetic action</button>
                <button id="two">Second synthetic action</button>
                HTML);
            $one = $page->locator('#one');
            $two = $page->locator('#two');
            $this->assertFalse($probe->focusState($one)->requireAvailable()['focused']);
            $this->assertTrue($probe->focus($one)->requireAvailable()['focused']);
            $this->assertTrue($probe->pressKey('Tab')->requireAvailable()['dispatched']);
            $this->assertTrue($probe->focusState($two)->requireAvailable()['focused']);
            $this->assertFalse($probe->focusState($one)->requireAvailable()['focused']);
            $probe->pressKey('Shift+Tab')->requireAvailable();
            $this->assertTrue($probe->focusState($one)->requireAvailable()['focused']);
            $probe->pressKey('Enter')->requireAvailable();
            $this->assertSame(1, $page->evaluate('() => window.localActions'));
            $probe->pressKey('Space')->requireAvailable();
            $this->assertSame(2, $page->evaluate('() => window.localActions'));

            $page->mouse()->move(500, 200);
            $before = $probe->layout($one)->requireAvailable();
            $probe->scroll(0, 120)->requireAvailable();
            $page->waitForFunction('() => window.scrollY > 0', null, ['timeout' => 5000]);
            $scrollY = $page->evaluate('() => window.scrollY');
            $after = $probe->layout($one)->requireAvailable();
            $this->assertGreaterThan(0, $scrollY);
            $this->assertEqualsWithDelta($before['y'] - $scrollY, $after['y'], 0.01);
            $this->assertTrue($after['clipped']);
            $this->assertEquals(0, $after['visibleFraction']);
        });
    }

    public function test_bounded_semantics_omit_local_content(): void
    {
        $this->withPage(function (PageInterface $page, BrowserProbe $probe): void {
            $page->setContent(<<<'HTML'
                <label for="check">example-sensitive-label</label>
                <span id="label">example-sensitive-description</span>
                <input id="check" type="checkbox" checked value="example-sensitive-value"
                    aria-label="example-sensitive-name" aria-labelledby="label">
                <div id="switch" role="switch" aria-checked="mixed" aria-expanded="false"
                    aria-selected="true" aria-disabled="true">example-sensitive-content</div>
                <div id="unknown" role="example-sensitive-role" aria-label="example-sensitive-name"></div>
                <h2 id="heading">example-sensitive-heading</h2>
                <button id="disabled" disabled>example-sensitive-button</button>
                HTML);
            $checkbox = $probe->semantics($page->locator('#check'));
            $this->assertSame([
                'role' => 'checkbox', 'checked' => true, 'expanded' => null, 'selected' => null,
                'disabled' => false, 'hasAriaLabel' => true, 'hasLabelledBy' => true, 'hasNativeLabel' => true,
            ], $checkbox->requireAvailable());
            $switch = $probe->semantics($page->locator('#switch'));
            $this->assertSame([
                'role' => 'switch', 'checked' => 'mixed', 'expanded' => false, 'selected' => true,
                'disabled' => true, 'hasAriaLabel' => false, 'hasLabelledBy' => false, 'hasNativeLabel' => false,
            ], $switch->requireAvailable());
            $unknown = $probe->semantics($page->locator('#unknown'));
            $this->assertNull($unknown->requireAvailable()['role']);
            $this->assertSame('heading', $probe->semantics($page->locator('#heading'))->requireAvailable()['role']);
            $this->assertFalse($probe->visibility($page->locator('#disabled'))->requireAvailable()['enabled']);
            foreach ([$checkbox, $switch, $unknown] as $observation) {
                $serialized = json_encode($observation->toArray(), JSON_THROW_ON_ERROR);
                $this->assertStringNotContainsString('example-sensitive', $serialized);
                $this->assertStringNotContainsString('#check', $serialized);
                $this->assertStringNotContainsString('<', $serialized);
            }
        });
    }

    public function test_media_and_responsive_viewport_are_observed(): void
    {
        $this->withPage(function (PageInterface $page, BrowserProbe $probe): void {
            $page->setContent(<<<'HTML'
                <style>
                    #responsive { width: 200px; height: 30px; }
                    @media (max-width: 500px) { #responsive { width: 100px; } }
                    @media (prefers-color-scheme: dark) { #responsive { height: 40px; } }
                </style>
                <div id="responsive"></div>
                HTML);
            $this->assertSame(['width' => 800, 'height' => 600, 'deviceScaleFactor' => 1], $probe->viewport()->requireAvailable());
            $initial = $probe->media()->requireAvailable();
            $this->assertContains($initial['colorScheme'], ['dark', 'light', 'no-preference']);
            $this->assertContains($initial['reducedMotion'], ['reduce', 'no-preference']);
            $this->assertIsBool($initial['forcedColors']);
            $dark = $probe->emulateMedia('dark', 'reduce')->requireAvailable();
            $this->assertSame('dark', $dark['colorScheme']);
            $this->assertSame('reduce', $dark['reducedMotion']);
            $this->assertSame($dark, $probe->media()->requireAvailable());
            $this->assertEquals(40, $probe->layout($page->locator('#responsive'))->requireAvailable()['height']);
            $this->assertSame(['width' => 400, 'height' => 300, 'deviceScaleFactor' => 1], $probe->resizeViewport(400, 300)->requireAvailable());
            $this->assertEquals(100, $probe->layout($page->locator('#responsive'))->requireAvailable()['width']);
            $light = $probe->emulateMedia('light', 'no-preference')->requireAvailable();
            $this->assertSame('light', $light['colorScheme']);
            $this->assertSame('no-preference', $light['reducedMotion']);
            $this->assertEquals(30, $probe->layout($page->locator('#responsive'))->requireAvailable()['height']);
        });
    }

    public function test_touch_and_unavailable_states_are_explicit(): void
    {
        $fixture = <<<'HTML'
            <script>window.localTapped = false;</script>
            <button id="tap" onclick="window.localTapped = true">Synthetic tap</button>
            HTML;
        $this->withPage(function (PageInterface $page, BrowserProbe $probe) use ($fixture): void {
            $page->setContent($fixture);
            $button = $page->locator('#tap');
            $observation = $probe->tap($button);
            $this->assertSame(BrowserObservationStatus::UNSUPPORTED, $observation->status);
            $this->assertSame('browser_probe_touch_unsupported', $observation->errorCode);
            $this->assertFalse($page->evaluate('() => window.localTapped'));
            $this->assertSame('browser_probe_contrast_unsupported', $probe->contrast($button)->errorCode);
        });
        $this->withPage(function (PageInterface $page, BrowserProbe $probe) use ($fixture): void {
            $page->setContent($fixture);
            $this->assertTrue($probe->tap($page->locator('#tap'))->requireAvailable()['dispatched']);
            $this->assertTrue($page->evaluate('() => window.localTapped'));
            $page->close();
            $closed = $probe->viewport();
            $this->assertSame(BrowserObservationStatus::UNAVAILABLE, $closed->status);
            $this->assertSame('browser_probe_page_closed', $closed->errorCode);
            $this->assertSame([], $closed->data);
        }, true);
    }

    public function test_geometry_and_focus_boundaries_are_unsupported_in_the_browser(): void
    {
        $this->withPage(function (PageInterface $page, BrowserProbe $probe): void {
            $page->setContent(<<<'HTML'
                <style>
                    .boundary { position: relative; width: 40px; height: 40px; }
                    #zoom { zoom: 2; }
                    #contain { contain: paint; }
                    #legacy { position: absolute; clip: rect(0px, 20px, 20px, 0px); }
                    #path { clip-path: inset(5px); }
                    #mask { mask-image: linear-gradient(black, transparent); }
                </style>
                <div class="boundary" id="zoom"></div><div class="boundary" id="contain"></div>
                <div class="boundary" id="legacy"></div><div class="boundary" id="path"></div>
                <div class="boundary" id="mask"></div><div id="deep"></div><div id="host"></div>
                <iframe id="frame" srcdoc="<button id='inside'>Synthetic frame</button>"></iframe>
                HTML);
            foreach (['#zoom', '#contain', '#legacy', '#path', '#mask'] as $selector) {
                $observation = $probe->layout($page->locator($selector));
                $this->assertSame(BrowserObservationStatus::UNSUPPORTED, $observation->status);
                $this->assertSame('browser_probe_capability_unsupported', $observation->errorCode);
                $this->assertSame([], $observation->data);
            }
            $page->locator('#deep')->evaluate(<<<'JS'
                (el) => {
                    let parent = el;
                    for (let i = 0; i < 65; i++) {
                        const child = document.createElement('div'); parent.append(child); parent = child;
                    }
                    parent.id = 'too-deep'; parent.style.width = '20px'; parent.style.height = '20px';
                }
                JS);
            $this->assertSame('browser_probe_capability_unsupported', $probe->layout($page->locator('#too-deep'))->errorCode);
            $page->locator('#host')->evaluate(<<<'JS'
                (el) => {
                    const root = el.attachShadow({ mode: 'open' });
                    const button = document.createElement('button'); button.id = 'shadow-button'; root.append(button);
                }
                JS);
            $shadow = $page->locator('#shadow-button');
            $this->assertSame('browser_probe_capability_unsupported', $probe->layout($shadow)->errorCode);
            $this->assertSame('browser_probe_capability_unsupported', $probe->focus($shadow)->errorCode);
            $inside = $page->frameLocator('#frame')->locator('#inside');
            $this->assertSame('browser_probe_capability_unsupported', $probe->layout($inside)->errorCode);
            $this->assertSame('browser_probe_capability_unsupported', $probe->focusState($inside)->errorCode);
        });
    }

    /** Every fixture owns and closes its browser, context, and PHP bridge. */
    private function withPage(Closure $test, bool $hasTouch = false): void
    {
        $client = PlaywrightFactory::create(new PlaywrightConfig(timeoutMs: 5000), new NullLogger);
        $browser = null;
        $browserContext = null;
        $context = null;
        try {
            $browser = $client->chromium()->withChannel('chrome')->withHeadless(true)->launch();
            $browserContext = $browser->newContext([
                'viewport' => ['width' => 800, 'height' => 600], 'hasTouch' => $hasTouch,
            ]);
            $browserContext->route('**/*', static fn (RouteInterface $route) => $route->abort());
            $context = new TestContext($browserContext, 5000);
            $test($context->page, $context->browserProbe);
        } finally {
            // Nested finally blocks also close the bridge if an earlier cleanup fails.
            try {
                if ($context !== null) {
                    $context->close();
                    $this->assertTrue($context->isClosed());
                } elseif ($browserContext !== null) {
                    $browserContext->close();
                }
            } finally {
                try {
                    $browser?->close();
                } finally {
                    $client->close();
                }
            }
        }
    }
}
