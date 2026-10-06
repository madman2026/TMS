<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Closure;
use Modules\Core\Contracts\BrowserProbe;
use Modules\Core\Data\BrowserObservation;
use Modules\Core\Exceptions\BrowserProbeException;
use Playwright\Locator\LocatorInterface;
use Playwright\Page\PageInterface;
use Throwable;

/**
 * Fixed browser-side normalization; no caller expression or raw target content.
 * Engine evidence currently covers local headless Chrome only. Locators must
 * belong to this page, and sensitive targets require later evidence safety.
 */
final class PlaywrightBrowserProbe implements BrowserProbe
{
    private const KEYS = [
        'Tab', 'Shift+Tab', 'Enter', 'Space', 'Escape', 'ArrowUp', 'ArrowDown',
        'ArrowLeft', 'ArrowRight', 'Home', 'End', 'PageUp', 'PageDown',
    ];

    private const FOCUS = <<<'JS'
        (el) => {
            const doc = el.ownerDocument;
            if (doc.defaultView !== doc.defaultView.top || el.getRootNode() !== doc)
                return { supported: false };
            return { supported: true, focused: doc.activeElement === el };
        }
        JS;

    private const MEDIA = <<<'JS'
        () => {
            if (typeof window.matchMedia !== 'function') return { supported: false };
            return { supported: true,
                colorScheme: matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                    (matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'no-preference'),
                reducedMotion: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'reduce' : 'no-preference',
                forcedColors: matchMedia('(forced-colors: active)').matches };
        }
        JS;

    public function __construct(private readonly PageInterface $page, private readonly int $timeoutMs)
    {
        if ($timeoutMs < 1) {
            throw new BrowserProbeException('browser_probe_invalid_argument');
        }
    }

    public function visibility(LocatorInterface $locator): BrowserObservation
    {
        return $this->observe('visibility', fn () => [
            'visible' => $locator->isVisible(), 'enabled' => $locator->isEnabled(),
        ], $locator);
    }

    public function layout(LocatorInterface $locator): BrowserObservation
    {
        return $this->observe('layout', function () use ($locator): array|BrowserObservation {
            $box = $locator->boundingBox(['timeout' => $this->actionTimeout()]);
            if ($box === null) {
                return BrowserObservation::unavailable('layout', 'browser_probe_geometry_unavailable');
            }
            // Validate coordinates before browser-side arithmetic using the same DTO bounds.
            $validated = BrowserObservation::available('layout', [
                'x' => $box['x'] ?? null, 'y' => $box['y'] ?? null,
                'width' => $box['width'] ?? null, 'height' => $box['height'] ?? null,
                'visibleFraction' => 0, 'clipped' => false, 'overflowX' => false, 'overflowY' => false,
            ])->data;
            $geometry = array_intersect_key($validated, array_flip(['x', 'y', 'width', 'height']));
            $result = $locator->evaluate(<<<'JS'
                (el, box) => {
                    const doc = el.ownerDocument, win = doc.defaultView;
                    if (win !== win.top || el.getRootNode() !== doc) return { supported: false };
                    let left = Math.max(0, box.x), top = Math.max(0, box.y);
                    let right = Math.min(win.innerWidth, box.x + box.width);
                    let bottom = Math.min(win.innerHeight, box.y + box.height);
                    let node = el, depth = 0;
                    while (node) {
                        if (++depth > 64) return { supported: false };
                        const css = win.getComputedStyle(node);
                        if ((css.zoom !== '1' && css.zoom !== 'normal') || css.clip !== 'auto' ||
                            css.contain.split(/\s+/).some(value => ['paint', 'strict', 'content'].includes(value)) ||
                            css.transform !== 'none' || css.rotate !== 'none' || css.scale !== 'none' ||
                            css.translate !== 'none' || css.perspective !== 'none' ||
                            css.clipPath !== 'none' || css.maskImage !== 'none') return { supported: false };
                        if (node !== el) {
                            const rect = node.getBoundingClientRect();
                            if (['hidden', 'clip', 'auto', 'scroll'].includes(css.overflowX)) {
                                left = Math.max(left, rect.left + node.clientLeft);
                                right = Math.min(right, rect.left + node.clientLeft + node.clientWidth);
                            }
                            if (['hidden', 'clip', 'auto', 'scroll'].includes(css.overflowY)) {
                                top = Math.max(top, rect.top + node.clientTop);
                                bottom = Math.min(bottom, rect.top + node.clientTop + node.clientHeight);
                            }
                        }
                        node = node.parentElement;
                    }
                    const area = box.width * box.height;
                    const visibleArea = Math.max(0, right - left) * Math.max(0, bottom - top);
                    return { supported: true, visibleFraction: area > 0 ? visibleArea / area : 0,
                        clipped: visibleArea < area, overflowX: el.scrollWidth > el.clientWidth,
                        overflowY: el.scrollHeight > el.clientHeight };
                }
                JS, $geometry);
            $normalized = $this->supportedData('layout', $result);
            if (is_array($normalized) && (count($normalized) !== 4
                || array_diff_key($normalized, array_flip(['visibleFraction', 'clipped', 'overflowX', 'overflowY'])) !== [])) {
                throw new BrowserProbeException('browser_probe_invalid_payload');
            }

            return $normalized instanceof BrowserObservation ? $normalized : [...$geometry, ...$normalized];
        }, $locator);
    }

    public function focusState(LocatorInterface $locator): BrowserObservation
    {
        return $this->observe('focus', fn () => $this->supportedData('focus', $locator->evaluate(self::FOCUS)), $locator);
    }

    public function focus(LocatorInterface $locator): BrowserObservation
    {
        return $this->observe('focus', function () use ($locator): array|BrowserObservation {
            $before = $this->supportedData('focus', $locator->evaluate(self::FOCUS));
            if ($before instanceof BrowserObservation) {
                return $before;
            }
            BrowserObservation::available('focus', $before);
            $locator->focus();

            return $this->supportedData('focus', $locator->evaluate(self::FOCUS));
        }, $locator);
    }

    public function pressKey(string $key): BrowserObservation
    {
        if (! in_array($key, self::KEYS, true)) {
            return BrowserObservation::unavailable('keyboard', 'browser_probe_invalid_argument');
        }

        return $this->observe('keyboard', function () use ($key): array {
            $this->page->keyboard()->press($key);

            return ['dispatched' => true];
        });
    }

    public function scroll(float $deltaX, float $deltaY): BrowserObservation
    {
        if (! is_finite($deltaX) || ! is_finite($deltaY) || abs($deltaX) > 10000 || abs($deltaY) > 10000) {
            return BrowserObservation::unavailable('scroll', 'browser_probe_invalid_argument');
        }

        return $this->observe('scroll', function () use ($deltaX, $deltaY): array {
            $this->page->mouse()->wheel($deltaX, $deltaY);

            return ['dispatched' => true];
        });
    }

    /**
     * Only declared role/state and label-source presence, never a computed name.
     * Implicit roles: buttons/links, common inputs/textarea/select/option,
     * headings/images/lists. Unsupported input types and unknown roles are null.
     */
    public function semantics(LocatorInterface $locator): BrowserObservation
    {
        return $this->observe('semantics', fn () => $locator->evaluate(<<<'JS'
            (el, roles) => {
                const boolean = value => value === 'true' ? true : value === 'false' ? false : null;
                const declared = el.getAttribute('role');
                let role = declared ? declared.trim().split(/\s+/).find(value => roles.includes(value)) ?? null : null;
                const tag = el.localName, type = tag === 'input' ? el.type : null;
                if (!declared) {
                    if (tag === 'button') role = 'button';
                    else if (tag === 'a' && el.hasAttribute('href')) role = 'link';
                    else if (tag === 'textarea') role = 'textbox';
                    else if (tag === 'input') role = ({ text: 'textbox', email: 'textbox', tel: 'textbox',
                        url: 'textbox', search: 'searchbox', checkbox: 'checkbox', radio: 'radio',
                        range: 'slider', number: 'spinbutton', button: 'button', submit: 'button', reset: 'button' })[type] ?? null;
                    else if (tag === 'select') role = el.multiple || el.size > 1 ? 'listbox' : 'combobox';
                    else if (tag === 'option') role = 'option';
                    else if (/^h[1-6]$/.test(tag)) role = 'heading';
                    else if (tag === 'img') role = el.getAttribute('alt') === '' ? 'presentation' : 'img';
                    else if (tag === 'ul' || tag === 'ol') role = 'list';
                    else if (tag === 'li') role = 'listitem';
                }
                const ariaChecked = el.getAttribute('aria-checked');
                return { role,
                    checked: tag === 'input' && ['checkbox', 'radio'].includes(type)
                        ? (el.indeterminate ? 'mixed' : el.checked)
                        : (ariaChecked === 'mixed' ? 'mixed' : boolean(ariaChecked)),
                    expanded: boolean(el.getAttribute('aria-expanded')),
                    selected: tag === 'option' ? el.selected : boolean(el.getAttribute('aria-selected')),
                    disabled: el.matches(':disabled') || el.getAttribute('aria-disabled') === 'true',
                    hasAriaLabel: Boolean(el.getAttribute('aria-label')?.trim()),
                    hasLabelledBy: Boolean(el.getAttribute('aria-labelledby')?.trim()),
                    hasNativeLabel: Boolean(el.labels?.length) };
            }
            JS, BrowserObservation::ROLES), $locator);
    }

    public function media(): BrowserObservation
    {
        return $this->observe('media', fn () => $this->supportedData('media', $this->page->evaluate(self::MEDIA)));
    }

    public function emulateMedia(string $colorScheme, string $reducedMotion): BrowserObservation
    {
        if (! in_array($colorScheme, ['dark', 'light', 'no-preference', 'no-override'], true)
            || ! in_array($reducedMotion, ['reduce', 'no-preference', 'no-override'], true)) {
            return BrowserObservation::unavailable('media', 'browser_probe_invalid_argument');
        }

        return $this->observe('media', function () use ($colorScheme, $reducedMotion): array|BrowserObservation {
            $before = $this->supportedData('media', $this->page->evaluate(self::MEDIA));
            if ($before instanceof BrowserObservation) {
                return $before;
            }
            BrowserObservation::available('media', $before);
            $this->page->emulateMedia(['colorScheme' => $colorScheme, 'reducedMotion' => $reducedMotion]);

            return $this->supportedData('media', $this->page->evaluate(self::MEDIA));
        });
    }

    public function viewport(): BrowserObservation
    {
        return $this->observe('viewport', fn () => $this->viewportData());
    }

    public function resizeViewport(int $width, int $height): BrowserObservation
    {
        if ($width < 1 || $height < 1 || $width > 16384 || $height > 16384) {
            return BrowserObservation::unavailable('viewport', 'browser_probe_invalid_argument');
        }

        return $this->observe('viewport', function () use ($width, $height): mixed {
            $this->page->setViewportSize($width, $height);

            return $this->viewportData();
        });
    }

    public function tap(LocatorInterface $locator): BrowserObservation
    {
        return $this->observe('touch', function () use ($locator): array|BrowserObservation {
            $points = $this->page->evaluate('() => navigator.maxTouchPoints');
            if (! is_int($points) || $points < 0 || $points > 100) {
                throw new BrowserProbeException('browser_probe_invalid_payload');
            }
            if ($points === 0) {
                return BrowserObservation::unsupported('touch', 'browser_probe_touch_unsupported');
            }
            $locator->tap(['timeout' => $this->actionTimeout()]);

            return ['dispatched' => true];
        }, $locator);
    }

    public function contrast(LocatorInterface $locator): BrowserObservation
    {
        return BrowserObservation::unsupported('contrast', 'browser_probe_contrast_unsupported');
    }

    private function observe(string $operation, Closure $action, ?LocatorInterface $locator = null): BrowserObservation
    {
        try {
            if ($this->page->isClosed()) {
                return BrowserObservation::unavailable($operation, 'browser_probe_page_closed');
            }
            if ($locator !== null) {
                $count = $locator->count();
                if ($count < 0) {
                    throw new BrowserProbeException('browser_probe_invalid_payload');
                }
                if ($count !== 1) {
                    return BrowserObservation::unavailable($operation, $count === 0
                        ? 'browser_probe_locator_missing' : 'browser_probe_locator_ambiguous');
                }
            }
            $result = $action();
            if ($result instanceof BrowserObservation) {
                return $result;
            }
            if (! is_array($result)) {
                throw new BrowserProbeException('browser_probe_invalid_payload');
            }

            return BrowserObservation::available($operation, $result);
        } catch (BrowserProbeException $exception) {
            return BrowserObservation::unavailable($operation, $exception->errorCode);
        } catch (Throwable) {
            // The raw cause may contain DOM content or selector diagnostics.
            return BrowserObservation::unavailable($operation, 'browser_probe_operation_failed');
        }
    }

    /** Validate internal support envelopes as strictly as public evidence. */
    private function supportedData(string $operation, mixed $result): array|BrowserObservation
    {
        if (! is_array($result) || ! is_bool($result['supported'] ?? null)) {
            throw new BrowserProbeException('browser_probe_invalid_payload');
        }
        if (! $result['supported']) {
            if (count($result) !== 1) {
                throw new BrowserProbeException('browser_probe_invalid_payload');
            }

            return BrowserObservation::unsupported($operation, 'browser_probe_capability_unsupported');
        }
        unset($result['supported']);

        return $result;
    }

    private function viewportData(): mixed
    {
        return $this->page->evaluate('() => ({ width: window.innerWidth, height: window.innerHeight, deviceScaleFactor: window.devicePixelRatio })');
    }

    private function actionTimeout(): int
    {
        return min($this->timeoutMs, 5000);
    }
}
