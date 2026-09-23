<?php

namespace Modules\Core\Data;

use Modules\Core\Exceptions\AcceptanceExecutionException;

final readonly class RunOptions
{
    /**
     * @param  array<string, mixed>  $contextOptions
     * @param  array<int, string>  $launchArguments
     */
    public function __construct(
        public string $browser = 'chromium',
        public bool $headless = true,
        public int $timeoutMs = 30_000,
        public int $slowMoMs = 0,
        public array $contextOptions = [],
        public array $launchArguments = [],
    ) {
        if (! in_array($this->browser, ['chromium', 'firefox', 'webkit'], true)) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        if ($this->timeoutMs < 1 || $this->slowMoMs < 0) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        if (array_intersect(['headless', 'slowMo', 'args', 'context'], array_keys($this->contextOptions)) !== []) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        foreach ($this->launchArguments as $argument) {
            if (! is_string($argument) || $argument === '') {
                throw AcceptanceExecutionException::configurationInvalid();
            }
        }

        $viewport = $this->contextOptions['viewport'] ?? null;
        if ($viewport !== null && (! is_array($viewport)
            || ! is_int($viewport['width'] ?? null)
            || ! is_int($viewport['height'] ?? null)
            || $viewport['width'] < 1
            || $viewport['height'] < 1)) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        $deviceScaleFactor = $this->contextOptions['deviceScaleFactor'] ?? null;
        if ($deviceScaleFactor !== null
            && (! is_int($deviceScaleFactor) && ! is_float($deviceScaleFactor)
                || $deviceScaleFactor <= 0)) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        foreach (['isMobile', 'hasTouch'] as $booleanOption) {
            if (array_key_exists($booleanOption, $this->contextOptions)
                && ! is_bool($this->contextOptions[$booleanOption])) {
                throw AcceptanceExecutionException::configurationInvalid();
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function launchOptions(): array
    {
        $options = [
            'headless' => $this->headless,
            'context' => $this->contextOptions,
        ];

        if ($this->slowMoMs > 0) {
            $options['slowMo'] = $this->slowMoMs;
        }

        if ($this->launchArguments !== []) {
            $options['args'] = $this->launchArguments;
        }

        return $options;
    }
}
