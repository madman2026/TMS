<?php

namespace Modules\Core\Tests\Unit;

use Closure;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\BrowserFactory;
use Modules\Core\Contracts\StepResult;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\RunOptions;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Modules\Core\Services\AcceptanceRunner;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Playwright\Browser\BrowserContextInterface;
use Playwright\Page\PageInterface;
use RuntimeException;

class AcceptanceRunnerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_runs_ordered_steps_in_one_context_and_closes_once(): void
    {
        [$runner, $browser] = $this->runnerWithBrowser();
        $contexts = [];
        $scenario = $this->scenario(function (TestContext $context) use (&$contexts): iterable {
            $contexts[] = spl_object_id($context);
            yield new StepResult(name: 'first', passed: true);
            $contexts[] = spl_object_id($context);
            yield new StepResult(name: 'second', passed: true);
        });

        $result = $runner->run($this->app(), $scenario, new RunOptions);

        $this->assertTrue($result->passed);
        $this->assertSame(['first', 'second'], array_column($result->steps, 'name'));
        $this->assertCount(1, array_unique($contexts));
        $browser->shouldHaveReceived('close')->once();
    }

    public function test_a_critical_failure_stops_later_steps_and_closes_once(): void
    {
        [$runner, $browser] = $this->runnerWithBrowser();
        $laterStepRan = false;
        $scenario = $this->scenario(function (TestContext $context) use (&$laterStepRan): iterable {
            yield new StepResult(
                name: 'critical failure',
                passed: false,
                error: 'Step failed.',
                errorCode: 'acceptance_step_failed',
                critical: true,
            );
            $laterStepRan = true;
            yield new StepResult(name: 'must not run', passed: true);
        });

        $result = $runner->run($this->app(), $scenario, new RunOptions);

        $this->assertFalse($result->passed);
        $this->assertSame('acceptance_step_failed', $result->errorCode);
        $this->assertCount(1, $result->steps);
        $this->assertFalse($laterStepRan);
        $browser->shouldHaveReceived('close')->once();
    }

    public function test_a_non_critical_failure_allows_continuation_but_fails_the_run(): void
    {
        [$runner, $browser] = $this->runnerWithBrowser();
        $laterStepRan = false;
        $scenario = $this->scenario(function (TestContext $context) use (&$laterStepRan): iterable {
            yield new StepResult(
                name: 'non-critical failure',
                passed: false,
                error: 'Step failed.',
                errorCode: 'acceptance_step_failed',
                critical: false,
            );
            $laterStepRan = true;
            yield new StepResult(name: 'continued', passed: true);
        });

        $result = $runner->run($this->app(), $scenario, new RunOptions);

        $this->assertFalse($result->passed);
        $this->assertTrue($laterStepRan);
        $this->assertCount(2, $result->steps);
        $browser->shouldHaveReceived('close')->once();
    }

    public function test_it_normalizes_an_unexpected_scenario_failure_and_closes_once(): void
    {
        [$runner, $browser] = $this->runnerWithBrowser();
        $scenario = $this->scenario(function (TestContext $context): iterable {
            throw new RuntimeException('example-sensitive-value');
            yield;
        });

        try {
            $runner->run($this->app(), $scenario, new RunOptions);
            $this->fail('Expected an AcceptanceExecutionException.');
        } catch (AcceptanceExecutionException $exception) {
            $this->assertSame('acceptance_scenario_failed', $exception->errorCode);
            $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
        }

        $browser->shouldHaveReceived('close')->once();
    }

    public function test_it_normalizes_a_browser_start_failure(): void
    {
        $factory = Mockery::mock(BrowserFactory::class);
        $factory->shouldReceive('create')
            ->once()
            ->andThrow(new RuntimeException('example-sensitive-value'));

        $runner = new AcceptanceRunner($factory);

        try {
            $runner->run($this->app(), $this->scenario(fn (): array => []), new RunOptions);
            $this->fail('Expected an AcceptanceExecutionException.');
        } catch (AcceptanceExecutionException $exception) {
            $this->assertSame('acceptance_browser_start_failed', $exception->errorCode);
            $this->assertTrue($exception->retryable);
            $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
        }
    }

    public function test_context_close_is_idempotent(): void
    {
        $page = Mockery::mock(PageInterface::class);
        $browser = Mockery::mock(BrowserContextInterface::class);
        $browser->shouldReceive('setDefaultTimeout')->once()->with(30_000);
        $browser->shouldReceive('setDefaultNavigationTimeout')->once()->with(30_000);
        $browser->shouldReceive('newPage')->once()->andReturn($page);
        $browser->shouldReceive('close')->once();
        $context = new TestContext($browser, 30_000);

        $context->close();
        $context->close();

        $this->assertTrue($context->isClosed());
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    #[DataProvider('invalidRunOptionsProvider')]
    public function test_invalid_options_fail_before_browser_launch(array $arguments): void
    {
        try {
            new RunOptions(...$arguments);
            $this->fail('Expected an AcceptanceExecutionException.');
        } catch (AcceptanceExecutionException $exception) {
            $this->assertSame('acceptance_configuration_invalid', $exception->errorCode);
            $this->assertFalse($exception->retryable);
        }
    }

    public function test_run_options_keep_launch_and_context_options_separate(): void
    {
        $options = new RunOptions(
            headless: false,
            slowMoMs: 25,
            contextOptions: [
                'viewport' => ['width' => 800, 'height' => 600],
                'deviceScaleFactor' => 2,
                'isMobile' => true,
                'hasTouch' => true,
            ],
            launchArguments: ['--example-argument'],
        );

        $this->assertSame([
            'headless' => false,
            'context' => [
                'viewport' => ['width' => 800, 'height' => 600],
                'deviceScaleFactor' => 2,
                'isMobile' => true,
                'hasTouch' => true,
            ],
            'slowMo' => 25,
            'args' => ['--example-argument'],
        ], $options->launchOptions());
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function invalidRunOptionsProvider(): array
    {
        return [
            'unsupported browser' => [['browser' => 'unsupported']],
            'zero timeout' => [['timeoutMs' => 0]],
            'negative slow motion' => [['slowMoMs' => -1]],
            'invalid viewport' => [['contextOptions' => ['viewport' => ['width' => 0, 'height' => 600]]]],
            'invalid scale factor' => [['contextOptions' => ['deviceScaleFactor' => 0]]],
            'invalid mobile flag' => [['contextOptions' => ['isMobile' => 'yes']]],
            'invalid touch flag' => [['contextOptions' => ['hasTouch' => 1]]],
            'launch option in context' => [['contextOptions' => ['headless' => true]]],
            'invalid launch argument' => [['launchArguments' => ['']]],
        ];
    }

    /**
     * @return array{AcceptanceRunner, BrowserContextInterface}
     */
    private function runnerWithBrowser(): array
    {
        $page = Mockery::mock(PageInterface::class);
        $browser = Mockery::mock(BrowserContextInterface::class);
        $browser->shouldReceive('setDefaultTimeout')->once()->with(30_000);
        $browser->shouldReceive('setDefaultNavigationTimeout')->once()->with(30_000);
        $browser->shouldReceive('newPage')->once()->andReturn($page);
        $browser->shouldReceive('close')->once();

        $factory = Mockery::mock(BrowserFactory::class);
        $factory->shouldReceive('create')->once()->andReturn($browser);

        return [new AcceptanceRunner($factory), $browser];
    }

    private function app(): AcceptanceApp
    {
        return new class implements AcceptanceApp
        {
            public function key(): string
            {
                return 'local-app';
            }

            public function scenarios(): iterable
            {
                return [];
            }
        };
    }

    private function scenario(Closure $steps): AcceptanceScenario
    {
        return new class($steps) implements AcceptanceScenario
        {
            public function __construct(private readonly Closure $steps) {}

            public function key(): string
            {
                return 'local-scenario';
            }

            public function name(): string
            {
                return 'Local scenario';
            }

            public function steps(TestContext $context): iterable
            {
                yield from ($this->steps)($context);
            }
        };
    }
}
