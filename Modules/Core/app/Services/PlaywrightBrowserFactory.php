<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\BrowserFactory;
use Modules\Core\Data\RunOptions;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Playwright\Browser\BrowserContextInterface;
use Playwright\Playwright;
use Throwable;

final class PlaywrightBrowserFactory implements BrowserFactory
{
    public function create(RunOptions $options): BrowserContextInterface
    {
        try {
            return match ($options->browser) {
                'chromium' => Playwright::chromium($options->launchOptions()),
                'firefox' => Playwright::firefox($options->launchOptions()),
                'webkit' => Playwright::webkit($options->launchOptions()),
                default => throw AcceptanceExecutionException::configurationInvalid(),
            };
        } catch (AcceptanceExecutionException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw AcceptanceExecutionException::browserStartFailed($exception);
        }
    }
}
