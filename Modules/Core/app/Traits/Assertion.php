<?php

namespace Modules\Core\Traits;

use Modules\Core\Contracts\TestContext;
use Playwright\Page\PageInterface;
use RuntimeException;

trait Assertion
{
    /**
     * Assert that an element is visible on the page.
     */
    public function assertElementVisible(PageInterface $page, string $selector, int $timeout = 5000): bool
    {
        $deadline = microtime(true) + ($timeout / 1000);

        while (microtime(true) < $deadline) {
            if ($page->locator($selector)->isVisible()) {
                return true;
            }
            usleep(200000);
        }

        throw new RuntimeException("Element '{$selector}' not visible after {$timeout}ms");
    }

    /**
     * Assert that an element is hidden on the page.
     */
    public function assertElementHidden(PageInterface $page, string $selector, int $timeout = 5000): bool
    {
        $deadline = microtime(true) + ($timeout / 1000);

        while (microtime(true) < $deadline) {
            if (! $page->locator($selector)->isVisible()) {
                return true;
            }
            usleep(200000);
        }

        throw new RuntimeException("Element '{$selector}' still visible after {$timeout}ms");
    }

    /**
     * Assert that the text of an element contains a given string.
     */
    public function assertTextContains(PageInterface $page, string $selector, string $expectedText, int $timeout = 5000): bool
    {
        $deadline = microtime(true) + ($timeout / 1000);

        while (microtime(true) < $deadline) {
            $text = $page->locator($selector)->first()->textContent();
            if ($text !== null && str_contains($text, $expectedText)) {
                return true;
            }
            usleep(200000);
        }

        throw new RuntimeException("Text of element '{$selector}' does not contain '{$expectedText}' after {$timeout}ms");
    }

    /**
     * Assert that the current URL contains a given string.
     */
    public function assertUrlContains(PageInterface $page, string $expected, int $timeout = 5000): bool
    {
        $deadline = microtime(true) + ($timeout / 1000);

        while (microtime(true) < $deadline) {
            if (str_contains($page->url(), $expected)) {
                return true;
            }
            usleep(200000);
        }

        throw new RuntimeException("URL does not contain '{$expected}' after {$timeout}ms");
    }

    /**
     * Assert that the page title contains a given string.
     */
    public function assertTitleContains(PageInterface $page, string $expected, int $timeout = 5000): bool
    {
        $deadline = microtime(true) + ($timeout / 1000);

        while (microtime(true) < $deadline) {
            $title = $page->title();
            if (str_contains($title, $expected)) {
                return true;
            }
            usleep(200000);
        }

        throw new RuntimeException("Page title does not contain '{$expected}' after {$timeout}ms");
    }

    public function stepAssert(callable $assertion, TestContext $context, string $stepName, bool $critical = true)
    {
        return $this->step($stepName, fn() => $assertion(), null, $critical, $context);
    }

}
