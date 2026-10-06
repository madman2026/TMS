<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Modules\Core\Enums\BrowserObservationStatus;
use Modules\Core\Exceptions\BrowserProbeException;
use Modules\Core\Services\PlaywrightBrowserProbe;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Playwright\Input\KeyboardInterface;
use Playwright\Input\MouseInterface;
use Playwright\Locator\LocatorInterface;
use Playwright\Page\PageInterface;
use RuntimeException;

class PlaywrightBrowserProbeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[DataProvider('observationProvider')]
    public function test_valid_observations_are_normalized(string $method, string $operation, array $data, ?array $evaluated): void
    {
        [$probe, $page, $locator] = $this->fixture(in_array($method, ['visibility', 'layout', 'focusState', 'semantics'], true));
        if ($method === 'visibility') {
            $locator->shouldReceive('isVisible')->once()->andReturn(false);
            $locator->shouldReceive('isEnabled')->once()->andReturn(true);
        } elseif ($method === 'layout') {
            $locator->shouldReceive('boundingBox')->once()->with(['timeout' => 5000])->andReturn(self::box());
            $locator->shouldReceive('evaluate')->once()->withArgs(function (string $expression, array $argument): bool {
                return $this->safeExpression($expression) && $argument === self::box()
                    && str_contains($expression, 'depth > 64');
            })->andReturn($evaluated);
        } elseif ($method === 'semantics') {
            $locator->shouldReceive('evaluate')->once()->withArgs(fn (string $expression, array $roles): bool =>
                $this->safeExpression($expression) && in_array('checkbox', $roles, true)
                && str_contains($expression, 'hasNativeLabel'))->andReturn($evaluated);
        } elseif ($method === 'focusState') {
            $locator->shouldReceive('evaluate')->once()->withArgs(fn (string $expression): bool =>
                $this->safeExpression($expression) && str_contains($expression, 'doc.activeElement === el'))->andReturn($evaluated);
        } else {
            $page->shouldReceive('evaluate')->once()->withArgs(fn (string $expression): bool => $this->safeExpression($expression))->andReturn($evaluated);
        }
        $observation = in_array($method, ['media', 'viewport'], true) ? $probe->$method() : $probe->$method($locator);

        $this->assertSame($operation, $observation->operation);
        $this->assertSame(BrowserObservationStatus::AVAILABLE, $observation->status);
        $this->assertSame($data, $observation->requireAvailable());
        $this->assertNull($observation->errorCode);
    }

    public static function observationProvider(): iterable
    {
        yield ['visibility', 'visibility', ['visible' => false, 'enabled' => true], null];
        yield ['layout', 'layout', [...self::box(), 'visibleFraction' => 0.5, 'clipped' => true, 'overflowX' => false, 'overflowY' => true],
            ['supported' => true, 'visibleFraction' => 0.5, 'clipped' => true, 'overflowX' => false, 'overflowY' => true]];
        yield ['focusState', 'focus', ['focused' => false], ['supported' => true, 'focused' => false]];
        yield ['semantics', 'semantics', self::semantics(), self::semantics()];
        yield ['media', 'media', self::media(), ['supported' => true, ...self::media()]];
        yield ['viewport', 'viewport', self::viewport(), self::viewport()];
    }

    #[DataProvider('actionProvider')]
    public function test_native_actions_use_approved_arguments(string $method, array $arguments, string $operation, array $data): void
    {
        [$probe, $page, $locator] = $this->fixture(in_array($method, ['focus', 'tap'], true));
        switch ($method) {
            case 'focus':
                $locator->shouldReceive('evaluate')->twice()->andReturn(['supported' => true, 'focused' => false], ['supported' => true, 'focused' => true]);
                $locator->shouldReceive('focus')->once()->withNoArgs();
                $arguments = [$locator];
                break;
            case 'pressKey':
                $keyboard = Mockery::mock(KeyboardInterface::class);
                $page->shouldReceive('keyboard')->once()->andReturn($keyboard);
                $keyboard->shouldReceive('press')->once()->with($arguments[0]);
                break;
            case 'scroll':
                $mouse = Mockery::mock(MouseInterface::class);
                $page->shouldReceive('mouse')->once()->andReturn($mouse);
                $mouse->shouldReceive('wheel')->once()->with(...$arguments);
                break;
            case 'emulateMedia':
                $page->shouldReceive('evaluate')->twice()->andReturn(['supported' => true, ...self::media()]);
                $page->shouldReceive('emulateMedia')->once()->with(['colorScheme' => $arguments[0], 'reducedMotion' => $arguments[1]])->andReturn($page);
                break;
            case 'resizeViewport':
                $page->shouldReceive('setViewportSize')->once()->with(...$arguments)->andReturn($page);
                $page->shouldReceive('evaluate')->once()->andReturn($data);
                break;
            case 'tap':
                $page->shouldReceive('evaluate')->once()->with('() => navigator.maxTouchPoints')->andReturn(5);
                $locator->shouldReceive('tap')->once()->with(['timeout' => 5000]);
                $arguments = [$locator];
                break;
        }
        $observation = $probe->$method(...$arguments);

        $this->assertSame($operation, $observation->operation);
        $this->assertSame($data, $observation->requireAvailable());
        $this->assertNull($observation->errorCode);
    }

    public static function actionProvider(): iterable
    {
        yield ['focus', [], 'focus', ['focused' => true]];
        foreach (['Tab', 'Shift+Tab', 'Enter', 'Space', 'Escape', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Home', 'End', 'PageUp', 'PageDown'] as $key) {
            yield 'key '.$key => ['pressKey', [$key], 'keyboard', ['dispatched' => true]];
        }
        yield ['scroll', [-10000.0, 10000.0], 'scroll', ['dispatched' => true]];
        yield ['emulateMedia', ['dark', 'reduce'], 'media', self::media()];
        yield ['emulateMedia', ['no-override', 'no-override'], 'media', self::media()];
        yield ['resizeViewport', [1, 16384], 'viewport', ['width' => 1, 'height' => 16384, 'deviceScaleFactor' => 1]];
        yield ['tap', [], 'touch', ['dispatched' => true]];
    }

    #[DataProvider('invalidArgumentProvider')]
    public function test_invalid_arguments_do_not_dispatch(string $method, array $arguments): void
    {
        $page = Mockery::mock(PageInterface::class);
        $probe = new PlaywrightBrowserProbe($page, 30000);
        $observation = $probe->$method(...$arguments);

        $this->assertSame(BrowserObservationStatus::UNAVAILABLE, $observation->status);
        $this->assertSame('browser_probe_invalid_argument', $observation->errorCode);
        $this->assertSame([], $observation->data);
        $page->shouldNotHaveReceived('isClosed');
        $page->shouldNotHaveReceived('keyboard');
        $page->shouldNotHaveReceived('mouse');
        $page->shouldNotHaveReceived('evaluate');
        $page->shouldNotHaveReceived('emulateMedia');
        $page->shouldNotHaveReceived('setViewportSize');
    }

    public static function invalidArgumentProvider(): iterable
    {
        yield ['pressKey', ['example-sensitive-value']];
        yield ['pressKey', ['Control+V']];
        yield ['pressKey', ['a']];
        yield ['emulateMedia', ['example-sensitive-value', 'reduce']];
        yield ['emulateMedia', ['dark', 'invalid']];
        yield ['resizeViewport', [0, 600]];
        yield ['resizeViewport', [800, 16385]];
        yield ['scroll', [NAN, 1.0]];
        yield ['scroll', [1.0, INF]];
        yield ['scroll', [-10001.0, 0.0]];
        yield ['scroll', [0.0, 10001.0]];
    }

    public function test_invalid_constructor_timeout_makes_no_page_call(): void
    {
        $page = Mockery::mock(PageInterface::class);
        try {
            new PlaywrightBrowserProbe($page, 0);
            $this->fail('Invalid timeout was accepted.');
        } catch (BrowserProbeException $exception) {
            $this->assertSame('browser_probe_invalid_argument', $exception->errorCode);
            $this->assertFalse($exception->retryable);
            $this->assertNull($exception->getPrevious());
        }
        $page->shouldNotHaveReceived('isClosed');
    }

    #[DataProvider('preflightFailureProvider')]
    public function test_missing_ambiguous_closed_and_library_failures_are_explicit(string $method, string $condition, string $code): void
    {
        $page = Mockery::mock(PageInterface::class);
        $locator = Mockery::mock(LocatorInterface::class);
        $probe = new PlaywrightBrowserProbe($page, 30000);
        if ($condition === 'closed') {
            $page->shouldReceive('isClosed')->once()->andReturn(true);
        } elseif ($condition === 'page-failure') {
            $page->shouldReceive('isClosed')->once()->andThrow(new RuntimeException('example-sensitive-value'));
        } else {
            $page->shouldReceive('isClosed')->once()->andReturn(false);
            if ($condition === 'locator-failure') {
                $locator->shouldReceive('count')->once()->andThrow(new RuntimeException('example-sensitive-value'));
            } else {
                $locator->shouldReceive('count')->once()->andReturn($condition === 'missing' ? 0 : ($condition === 'ambiguous' ? 2 : -1));
            }
        }
        $observation = $probe->$method($locator);

        $this->assertSame(BrowserObservationStatus::UNAVAILABLE, $observation->status);
        $this->assertSame($code, $observation->errorCode);
        $this->assertSame([], $observation->data);
        $this->assertStringNotContainsString('example-sensitive-value', json_encode($observation->toArray(), JSON_THROW_ON_ERROR));
        $locator->shouldNotHaveReceived('focus');
        $locator->shouldNotHaveReceived('tap');
        $locator->shouldNotHaveReceived('evaluate');
    }

    public static function preflightFailureProvider(): iterable
    {
        foreach (['visibility', 'layout', 'focusState', 'focus', 'semantics', 'tap'] as $method) {
            foreach (['closed' => 'browser_probe_page_closed', 'missing' => 'browser_probe_locator_missing',
                'ambiguous' => 'browser_probe_locator_ambiguous', 'page-failure' => 'browser_probe_operation_failed',
                'locator-failure' => 'browser_probe_operation_failed', 'negative-count' => 'browser_probe_invalid_payload'] as $condition => $code) {
                yield $method.' '.$condition => [$method, $condition, $code];
            }
        }
    }

    #[DataProvider('unsupportedProvider')]
    public function test_unsupported_capabilities_do_not_fallback(string $method, string $code): void
    {
        if ($method === 'contrast') {
            $page = Mockery::mock(PageInterface::class);
            $locator = Mockery::mock(LocatorInterface::class);
            $probe = new PlaywrightBrowserProbe($page, 30000);
        } else {
            [$probe, $page, $locator] = $this->fixture($method !== 'media' && $method !== 'emulateMedia');
            if ($method === 'layout') {
                $locator->shouldReceive('boundingBox')->once()->andReturn(self::box());
            }
            if ($method === 'tap') {
                $page->shouldReceive('evaluate')->once()->andReturn(0);
            } elseif (in_array($method, ['media', 'emulateMedia'], true)) {
                $page->shouldReceive('evaluate')->once()->andReturn(['supported' => false]);
            } else {
                $locator->shouldReceive('evaluate')->once()->andReturn(['supported' => false]);
            }
        }
        $observation = match ($method) {
            'media' => $probe->media(),
            'emulateMedia' => $probe->emulateMedia('dark', 'reduce'),
            default => $probe->$method($locator),
        };

        $this->assertSame(BrowserObservationStatus::UNSUPPORTED, $observation->status);
        $this->assertSame($code, $observation->errorCode);
        $this->assertSame([], $observation->data);
        $locator->shouldNotHaveReceived('focus');
        $locator->shouldNotHaveReceived('tap');
        $locator->shouldNotHaveReceived('click');
        $page->shouldNotHaveReceived('emulateMedia');
    }

    public static function unsupportedProvider(): iterable
    {
        foreach (['layout', 'focusState', 'focus', 'media', 'emulateMedia'] as $method) {
            yield [$method, 'browser_probe_capability_unsupported'];
        }
        yield ['tap', 'browser_probe_touch_unsupported'];
        yield ['contrast', 'browser_probe_contrast_unsupported'];
    }

    #[DataProvider('rawPayloadProvider')]
    public function test_raw_content_cannot_escape(string $method, mixed $payload): void
    {
        [$probe, $page, $locator] = $this->fixture(! in_array($method, ['media', 'viewport', 'emulateMedia'], true));
        if ($method === 'layout') {
            $locator->shouldReceive('boundingBox')->once()->andReturn(self::box());
        }
        if (in_array($method, ['media', 'viewport', 'emulateMedia', 'tap'], true)) {
            $page->shouldReceive('evaluate')->once()->andReturn($payload);
        } else {
            $locator->shouldReceive('evaluate')->once()->andReturn($payload);
        }
        $observation = match ($method) {
            'media', 'viewport' => $probe->$method(),
            'emulateMedia' => $probe->emulateMedia('dark', 'reduce'),
            default => $probe->$method($locator),
        };

        $this->assertSame('browser_probe_invalid_payload', $observation->errorCode);
        $this->assertSame([], $observation->data);
        $this->assertStringNotContainsString('example-sensitive-value', json_encode($observation->toArray(), JSON_THROW_ON_ERROR));
        $locator->shouldNotHaveReceived('focus');
        $locator->shouldNotHaveReceived('tap');
        $page->shouldNotHaveReceived('emulateMedia');
        try {
            $observation->requireAvailable();
            $this->fail('Unsafe payload became available.');
        } catch (BrowserProbeException $exception) {
            $this->assertNull($exception->getPrevious());
            $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
        }
    }

    public static function rawPayloadProvider(): iterable
    {
        yield ['focusState', ['supported' => true, 'focused' => 'example-sensitive-value']];
        yield ['focus', ['supported' => true, 'focused' => 'example-sensitive-value']];
        yield ['focusState', ['supported' => false, 'raw' => 'example-sensitive-value']];
        yield ['focusState', ['supported' => 'false']];
        yield ['semantics', [...self::semantics(), 'role' => 'example-sensitive-value']];
        yield ['semantics', [...self::semantics(), 'raw' => 'example-sensitive-value']];
        yield ['media', ['supported' => true, ...self::media(), 'raw' => 'example-sensitive-value']];
        yield ['emulateMedia', ['supported' => true, ...self::media(), 'colorScheme' => 'example-sensitive-value']];
        yield ['viewport', [...self::viewport(), 'width' => INF]];
        yield ['viewport', ['raw' => 'example-sensitive-value']];
        yield ['layout', ['supported' => true, 'visibleFraction' => 1, 'clipped' => false, 'overflowX' => false, 'overflowY' => false, 'x' => 123]];
        yield ['layout', ['supported' => true, 'visibleFraction' => NAN, 'clipped' => false, 'overflowX' => false, 'overflowY' => false]];
        yield ['tap', 'example-sensitive-value'];
        yield ['tap', -1];
        yield ['tap', 101];
        yield ['viewport', null];
    }

    public function test_null_geometry_is_unavailable_and_raw_native_failure_is_omitted(): void
    {
        [$probe, $page, $locator] = $this->fixture(true);
        $locator->shouldReceive('boundingBox')->once()->andReturnNull();
        $this->assertSame('browser_probe_geometry_unavailable', $probe->layout($locator)->errorCode);
        $locator->shouldNotHaveReceived('evaluate');

        [$probe, $page, $locator] = $this->fixture(true);
        $locator->shouldReceive('isVisible')->once()->andThrow(new RuntimeException('example-sensitive-value'));
        $observation = $probe->visibility($locator);
        $this->assertSame('browser_probe_operation_failed', $observation->errorCode);
        $this->assertSame([], $observation->data);
        $this->assertStringNotContainsString('example-sensitive-value', json_encode($observation->toArray(), JSON_THROW_ON_ERROR));
    }

    private function fixture(bool $withLocator): array
    {
        $page = Mockery::mock(PageInterface::class);
        $locator = Mockery::mock(LocatorInterface::class);
        $page->shouldReceive('isClosed')->once()->andReturn(false);
        if ($withLocator) {
            $locator->shouldReceive('count')->once()->andReturn(1);
        }
        foreach (['getSelector', 'textContent', 'innerText', 'innerHTML', 'inputValue', 'ariaSnapshot', 'getAttribute'] as $method) {
            $locator->shouldNotReceive($method);
        }

        return [new PlaywrightBrowserProbe($page, 30000), $page, $locator];
    }

    private function safeExpression(string $expression): bool
    {
        foreach (['textContent', 'innerText', 'innerHTML', 'localStorage', 'sessionStorage', 'clipboard', 'console.', 'example-sensitive-value'] as $forbidden) {
            if (str_contains($expression, $forbidden)) {
                return false;
            }
        }

        return true;
    }

    private static function box(): array
    {
        return ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 40];
    }

    private static function semantics(): array
    {
        return ['role' => 'checkbox', 'checked' => 'mixed', 'expanded' => null, 'selected' => null,
            'disabled' => false, 'hasAriaLabel' => true, 'hasLabelledBy' => false, 'hasNativeLabel' => false];
    }

    private static function media(): array
    {
        return ['colorScheme' => 'dark', 'reducedMotion' => 'reduce', 'forcedColors' => false];
    }

    private static function viewport(): array
    {
        return ['width' => 800, 'height' => 600, 'deviceScaleFactor' => 1];
    }
}
