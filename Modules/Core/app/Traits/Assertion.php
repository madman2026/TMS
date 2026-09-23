<?php

namespace Modules\Core\Traits;

use Playwright\Page\PageInterface;

use function Playwright\Testing\expect;

trait Assertion
{
    /**
     * Assert that an element is visible on the page.
     */
    public function assertElementVisible(PageInterface $page, string $selector, int $timeout = 5000): bool
    {
        expect($page->locator($selector))->withTimeout($timeout)->toBeVisible();

        return true;
    }

    /**
     * Assert that an element is hidden on the page.
     */
    public function assertElementHidden(PageInterface $page, string $selector, int $timeout = 5000): bool
    {
        expect($page->locator($selector))->withTimeout($timeout)->toBeHidden();

        return true;
    }

    /**
     * Assert that the text of an element contains a given string.
     */
    public function assertTextContains(PageInterface $page, string $selector, string $expectedText, int $timeout = 5000): bool
    {
        expect($page->locator($selector)->first())->withTimeout($timeout)->toContainText($expectedText);

        return true;
    }

    /**
     * Assert that the current URL contains a given string.
     */
    public function assertUrl(PageInterface $page, string $expected, int $timeout = 5000): bool
    {
        expect($page)->withTimeout($timeout)->toHaveURL($expected);

        return true;
    }

    /**
     * Assert that the page title contains a given string.
     */
    public function assertTitle(PageInterface $page, string $expected, int $timeout = 5000): bool
    {
        expect($page)->withTimeout($timeout)->toHaveTitle($expected);

        return true;
    }

    public function stepAssert(callable $assertion, string $stepName, bool $critical = true)
    {
        return $this->step($stepName, fn () => $assertion(), null, $critical);
    }
}
