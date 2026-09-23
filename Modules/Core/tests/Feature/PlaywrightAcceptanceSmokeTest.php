<?php

namespace Modules\Core\Tests\Feature;

use App\Contracts\BaseAction;
use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\StepResult;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\RunOptions;
use Modules\Core\Services\AcceptanceRunner;
use Modules\Core\Services\PlaywrightBrowserFactory;
use Modules\Core\Traits\Assertion;
use Modules\Core\Traits\HasStep;
use PHPUnit\Framework\TestCase;

class PlaywrightAcceptanceSmokeTest extends TestCase
{
    public function test_two_local_steps_share_one_headless_chromium_context(): void
    {
        $scenario = new class implements AcceptanceScenario
        {
            public ?TestContext $context = null;

            /** @var array<int, int> */
            public array $contextIds = [];

            public function key(): string
            {
                return 'local-browser-smoke';
            }

            public function name(): string
            {
                return 'Local browser smoke';
            }

            public function steps(TestContext $context): iterable
            {
                $this->context = $context;
                $action = new class extends BaseAction
                {
                    use Assertion;
                    use HasStep;

                    public function interact(TestContext $context): StepResult
                    {
                        return $this->step('interact with local content', function () use ($context): void {
                            $context->page->setContent(<<<'HTML'
                                <button id="change" onclick="document.querySelector('#result').textContent = 'Done'">Change</button>
                                <div id="result">Waiting</div>
                                HTML);
                            $context->page->locator('#change')->click();
                            $this->assertTextContains($context->page, '#result', 'Done');
                        });
                    }

                    public function verifyViewport(TestContext $context): StepResult
                    {
                        return $this->step('verify context viewport', function () use ($context): void {
                            $viewport = $context->page->evaluate(
                                '() => ({ width: window.innerWidth, height: window.innerHeight })',
                            );

                            if ($viewport !== ['width' => 800, 'height' => 600]) {
                                throw new \RuntimeException('Configured viewport was not applied.');
                            }

                            $this->assertElementVisible($context->page, '#result');
                        });
                    }
                };

                $this->contextIds[] = spl_object_id($context);
                yield $action->interact($context);
                $this->contextIds[] = spl_object_id($context);
                yield $action->verifyViewport($context);
            }
        };

        $runner = new AcceptanceRunner(new PlaywrightBrowserFactory);
        $result = $runner->run($this->app($scenario), $scenario, new RunOptions(
            contextOptions: ['viewport' => ['width' => 800, 'height' => 600]],
        ));

        $this->assertTrue($result->passed);
        $this->assertCount(2, $result->steps);
        $this->assertCount(1, array_unique($scenario->contextIds));
        $this->assertTrue($scenario->context?->isClosed());
    }

    public function test_a_local_assertion_failure_is_normalized_and_closes_the_context(): void
    {
        $scenario = new class implements AcceptanceScenario
        {
            public ?TestContext $context = null;

            public bool $laterStepRan = false;

            public function key(): string
            {
                return 'local-browser-failure';
            }

            public function name(): string
            {
                return 'Local browser failure';
            }

            public function steps(TestContext $context): iterable
            {
                $this->context = $context;
                $action = new class extends BaseAction
                {
                    use Assertion;
                    use HasStep;

                    public function fail(TestContext $context): StepResult
                    {
                        return $this->step('expected local failure', function () use ($context): void {
                            $context->page->setContent('<div id="result">Actual</div>');
                            $this->assertTextContains($context->page, '#result', 'Expected', 250);
                        });
                    }
                };

                yield $action->fail($context);
                $this->laterStepRan = true;
                yield new StepResult(name: 'must not run', passed: true);
            }
        };

        $runner = new AcceptanceRunner(new PlaywrightBrowserFactory);
        $result = $runner->run($this->app($scenario), $scenario, new RunOptions);

        $this->assertFalse($result->passed);
        $this->assertSame('acceptance_step_failed', $result->errorCode);
        $this->assertFalse($scenario->laterStepRan);
        $this->assertTrue($scenario->context?->isClosed());
    }

    private function app(AcceptanceScenario $scenario): AcceptanceApp
    {
        return new class($scenario) implements AcceptanceApp
        {
            public function __construct(private readonly AcceptanceScenario $scenario) {}

            public function key(): string
            {
                return 'local-smoke-app';
            }

            public function scenarios(): iterable
            {
                yield $this->scenario;
            }
        };
    }
}
