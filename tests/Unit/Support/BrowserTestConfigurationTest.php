<?php

namespace Tests\Unit\Support;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Support\BrowserTestConfiguration;

class BrowserTestConfigurationTest extends TestCase
{
    private bool $hadEnvValue;

    private mixed $envValue;

    private bool $hadServerValue;

    private mixed $serverValue;

    private string|false $processValue;

    protected function setUp(): void
    {
        parent::setUp();

        $key = BrowserTestConfiguration::HEADLESS_ENV;
        $this->hadEnvValue = array_key_exists($key, $_ENV);
        $this->envValue = $_ENV[$key] ?? null;
        $this->hadServerValue = array_key_exists($key, $_SERVER);
        $this->serverValue = $_SERVER[$key] ?? null;
        $this->processValue = getenv($key);
    }

    protected function tearDown(): void
    {
        $key = BrowserTestConfiguration::HEADLESS_ENV;

        if ($this->hadEnvValue) {
            $_ENV[$key] = $this->envValue;
        } else {
            unset($_ENV[$key]);
        }

        if ($this->hadServerValue) {
            $_SERVER[$key] = $this->serverValue;
        } else {
            unset($_SERVER[$key]);
        }

        if ($this->processValue === false) {
            putenv($key);
        } else {
            putenv($key.'='.$this->processValue);
        }

        parent::tearDown();
    }

    public function test_phpunit_boolean_values_are_normalized(): void
    {
        $this->setHeadlessValue('1');
        $this->assertTrue(BrowserTestConfiguration::headless());

        $this->setHeadlessValue('');
        $this->assertFalse(BrowserTestConfiguration::headless());

        $this->setHeadlessValue('true');
        $this->assertTrue(BrowserTestConfiguration::headless());

        $this->setHeadlessValue('false');
        $this->assertFalse(BrowserTestConfiguration::headless());
    }

    public function test_missing_value_defaults_to_headless(): void
    {
        $this->setHeadlessValue(null);

        $this->assertTrue(BrowserTestConfiguration::headless());
    }

    public function test_invalid_value_is_rejected_without_echoing_it(): void
    {
        $this->setHeadlessValue('example-sensitive-value');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ACCEPTANCE_HEADLESS must be a boolean value.');
        $this->expectExceptionMessageMatches('/^(?!.*example-sensitive-value).*$/');

        BrowserTestConfiguration::headless();
    }

    private function setHeadlessValue(?string $value): void
    {
        $key = BrowserTestConfiguration::HEADLESS_ENV;

        if ($value === null) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);

            return;
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key.'='.$value);
    }
}
