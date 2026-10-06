<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\Data\BrowserObservation;
use Playwright\Locator\LocatorInterface;

/**
 * Bounded observations and native interactions for one page. Callers own
 * same-page locators and assertions; dispatch alone does not prove an outcome.
 * Sensitive surfaces require the separately governed evidence-safety boundary.
 */
interface BrowserProbe
{
    public function visibility(LocatorInterface $locator): BrowserObservation;

    public function layout(LocatorInterface $locator): BrowserObservation;

    public function focusState(LocatorInterface $locator): BrowserObservation;

    public function focus(LocatorInterface $locator): BrowserObservation;

    public function pressKey(string $key): BrowserObservation;

    public function scroll(float $deltaX, float $deltaY): BrowserObservation;

    public function semantics(LocatorInterface $locator): BrowserObservation;

    public function media(): BrowserObservation;

    public function emulateMedia(string $colorScheme, string $reducedMotion): BrowserObservation;

    public function viewport(): BrowserObservation;

    public function resizeViewport(int $width, int $height): BrowserObservation;

    public function tap(LocatorInterface $locator): BrowserObservation;

    public function contrast(LocatorInterface $locator): BrowserObservation;
}
