<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use Closure;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Modules\Core\Contracts\BrowserTargetObserver;
use Modules\Core\Contracts\BrowserTargetRuntime;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\BrowserTargetObservation;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Playwright\Browser\BrowserContextInterface;
use Playwright\Page\PageInterface;
use RuntimeException;

final class TestContextTargetObservationTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_exact_page_and_requested_key_reach_optional_observer(): void
    {
        $page = Mockery::mock(PageInterface::class);
        $browser = $this->browser($page);
        $runtime = new TargetObserverRuntime(
            static function (PageInterface $actualPage, string $key) use ($page): BrowserTargetObservation {
                self::assertSame($page, $actualPage);
                self::assertSame('operations-health', $key);

                return new BrowserTargetObservation($key, ['state' => 'ready']);
            },
        );
        $context = new TestContext($browser, $this->identity(), $runtime, 30_000);

        $observation = $context->observeTarget('operations-health');

        $this->assertSame('operations-health', $observation->key);
        $this->assertSame(['state' => 'ready'], $observation->facts);
        $this->assertSame(1, $runtime->observeCalls);
    }

    public function test_runtime_without_observer_capability_fails_before_observation(): void
    {
        $page = Mockery::mock(PageInterface::class);
        $browser = $this->browser($page);
        $runtime = Mockery::mock(BrowserTargetRuntime::class);
        $runtime->shouldReceive('prepare')->once()->with($browser);
        $context = new TestContext($browser, $this->identity(), $runtime, 30_000);

        $this->assertConfigurationInvalid(fn (): BrowserTargetObservation => $context->observeTarget('operations-health'));
    }

    #[DataProvider('invalidKeyProvider')]
    public function test_invalid_request_keys_fail_before_observer_call(string $key): void
    {
        $page = Mockery::mock(PageInterface::class);
        $runtime = new TargetObserverRuntime(
            static fn (): BrowserTargetObservation => throw new RuntimeException('observer-called-sentinel'),
        );
        $context = new TestContext($this->browser($page), $this->identity(), $runtime, 30_000);

        $this->assertConfigurationInvalid(fn (): BrowserTargetObservation => $context->observeTarget($key));
        $this->assertSame(0, $runtime->observeCalls);
    }

    public static function invalidKeyProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'uppercase' => ['Health'];
        yield 'unsafe separator' => ['health/raw'];
        yield 'arbitrary text' => ['health raw'];
        yield 'too long' => [str_repeat('a', 65)];
        yield 'invalid utf-8' => ["health\xFF"];
    }

    public function test_mismatched_returned_key_is_rejected_after_one_observation(): void
    {
        $runtime = new TargetObserverRuntime(
            static fn (): BrowserTargetObservation => new BrowserTargetObservation('other-health', ['state' => 'ready']),
        );
        $context = new TestContext(
            $this->browser(Mockery::mock(PageInterface::class)),
            $this->identity(),
            $runtime,
            30_000,
        );

        $this->assertConfigurationInvalid(fn (): BrowserTargetObservation => $context->observeTarget('operations-health'));
        $this->assertSame(1, $runtime->observeCalls);
    }

    public function test_malformed_or_raw_observer_failures_are_normalized_without_cause(): void
    {
        $failures = [
            'malformed return' => static fn (): object => new \stdClass,
            'raw exception' => static fn (): BrowserTargetObservation => throw new RuntimeException('example-sensitive-value'),
        ];

        foreach ($failures as $observer) {
            $runtime = new TargetObserverRuntime($observer);
            $context = new TestContext(
                $this->browser(Mockery::mock(PageInterface::class)),
                $this->identity(),
                $runtime,
                30_000,
            );

            $this->assertConfigurationInvalid(fn (): BrowserTargetObservation => $context->observeTarget('operations-health'));
            $this->assertSame(1, $runtime->observeCalls);
        }
    }

    public function test_already_normalized_observer_exception_is_preserved(): void
    {
        $expected = new AcceptanceExecutionException(
            'acceptance_scenario_failed',
            false,
            'The acceptance scenario could not be completed.',
        );
        $runtime = new TargetObserverRuntime(
            static fn (): BrowserTargetObservation => throw $expected,
        );
        $context = new TestContext(
            $this->browser(Mockery::mock(PageInterface::class)),
            $this->identity(),
            $runtime,
            30_000,
        );

        try {
            $context->observeTarget('operations-health');
            $this->fail('Normalized observer exception was not thrown.');
        } catch (AcceptanceExecutionException $exception) {
            $this->assertSame($expected, $exception);
            $this->assertSame('acceptance_scenario_failed', $exception->errorCode);
            $this->assertNull($exception->getPrevious());
        }
    }

    private function browser(PageInterface $page): BrowserContextInterface
    {
        $browser = Mockery::mock(BrowserContextInterface::class);
        $browser->shouldReceive('setDefaultTimeout')->once()->with(30_000);
        $browser->shouldReceive('setDefaultNavigationTimeout')->once()->with(30_000);
        $browser->shouldReceive('newPage')->once()->andReturn($page);

        return $browser;
    }

    private function identity(): AcceptanceExecutionIdentity
    {
        return new AcceptanceExecutionIdentity('app', 'component', 'suite', 'scenario', 'variant');
    }

    private function assertConfigurationInvalid(Closure $action): void
    {
        try {
            $action();
            $this->fail('Expected a configuration exception.');
        } catch (AcceptanceExecutionException $exception) {
            $this->assertSame('acceptance_configuration_invalid', $exception->errorCode);
            $this->assertFalse($exception->retryable);
            $this->assertSame('Acceptance run configuration is invalid.', $exception->getMessage());
            $this->assertNull($exception->getPrevious());
            $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
        }
    }
}

final class TargetObserverRuntime implements BrowserTargetObserver, BrowserTargetRuntime
{
    public int $observeCalls = 0;

    public function __construct(private readonly Closure $observer) {}

    public function prepare(BrowserContextInterface $browser): void {}

    public function open(PageInterface $page, string $surfaceKey): void {}

    public function observe(PageInterface $page, string $observationKey): BrowserTargetObservation
    {
        $this->observeCalls++;

        return ($this->observer)($page, $observationKey);
    }
}
