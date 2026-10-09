<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\StepResult;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\ExecutorRequest;
use Modules\Core\Data\RunOptions;
use Modules\Core\Data\RunResult;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Modules\Core\Enums\ExecutorResultStatus;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Modules\Core\Services\AcceptanceRunner;
use Modules\Core\Services\BrowserAcceptanceExecutor;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class BrowserAcceptanceExecutorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_delegates_once_and_returns_an_ordered_safe_summary(): void
    {
        $request = $this->request();
        $runner = Mockery::mock(AcceptanceRunner::class);
        $runner->shouldReceive('run')
            ->once()
            ->with($request->identity, $request->scenario, $request->runOptions)
            ->andReturn(new RunResult(
                identity: $request->identity,
                scenarioName: 'Safe scenario',
                passed: true,
                duration: 1.2345,
                steps: [
                    new StepResult(
                        name: 'First step',
                        passed: true,
                        duration: 0.1254,
                        results: ['raw' => 'sensitive-result'],
                        description: 'sensitive-description',
                        exceptionClass: 'SensitiveException',
                    ),
                    new StepResult(name: 'Second step', passed: true, duration: 0.001),
                ],
            ));

        $result = (new BrowserAcceptanceExecutor($runner))->execute($request);
        $serialized = serialize($result);

        $this->assertSame(ExecutorResultStatus::Succeeded, $result->status);
        $this->assertSame(1235, $result->data->durationMs);
        $this->assertSame(['First step', 'Second step'], array_column($result->data->steps, 'name'));
        $this->assertSame([125, 1], array_column($result->data->steps, 'durationMs'));
        $this->assertStringNotContainsString('sensitive-result', $serialized);
        $this->assertStringNotContainsString('sensitive-description', $serialized);
        $this->assertStringNotContainsString('SensitiveException', $serialized);
    }

    public function test_it_returns_a_permanent_failure_for_a_failed_run(): void
    {
        $request = $this->request();
        $runner = Mockery::mock(AcceptanceRunner::class);
        $runner->shouldReceive('run')->once()->andReturn(new RunResult(
            $request->identity,
            'Safe scenario',
            false,
            0.1,
            [new StepResult('Failed step', false, 'sensitive-error', 'assertion_failed')],
            'assertion_failed',
        ));

        $result = (new BrowserAcceptanceExecutor($runner))->execute($request);

        $this->assertSame('browser_execution_failed', $result->errorCode);
        $this->assertFalse($result->retryable);
        $this->assertTrue($result->permanent);
        $this->assertStringNotContainsString('sensitive-error', serialize($result));
    }

    public function test_it_preserves_safe_runner_retryability_without_leaking_the_exception(): void
    {
        foreach ([true, false] as $retryable) {
            $request = $this->request();
            $runner = Mockery::mock(AcceptanceRunner::class);
            $runner->shouldReceive('run')->once()->andThrow(new AcceptanceExecutionException(
                'acceptance_browser_start_failed',
                $retryable,
                'sensitive-exception-message',
            ));

            $result = (new BrowserAcceptanceExecutor($runner))->execute($request);

            $this->assertSame('browser_execution_failed', $result->errorCode);
            $this->assertSame($retryable, $result->retryable);
            $this->assertSame(! $retryable, $result->permanent);
            $this->assertSame($retryable, $result->adminActionRequired);
            $this->assertStringNotContainsString('sensitive-exception-message', serialize($result));
        }
    }

    public function test_it_normalizes_unexpected_or_unsafe_runner_output(): void
    {
        $request = $this->request();
        $unexpected = Mockery::mock(AcceptanceRunner::class);
        $unexpected->shouldReceive('run')->once()->andThrow(new RuntimeException('sensitive-unexpected'));
        $unexpectedResult = (new BrowserAcceptanceExecutor($unexpected))->execute($request);

        $unsafe = Mockery::mock(AcceptanceRunner::class);
        $unsafe->shouldReceive('run')->once()->andReturn(new RunResult(
            $request->identity,
            "Unsafe\0name",
            true,
            NAN,
            [],
        ));
        $unsafeResult = (new BrowserAcceptanceExecutor($unsafe))->execute($request);

        $mismatchedIdentity = Mockery::mock(AcceptanceRunner::class);
        $mismatchedIdentity->shouldReceive('run')->once()->andReturn(new RunResult(
            new AcceptanceExecutionIdentity('other', 'component', 'suite', 'scenario', 'variant'),
            'Safe scenario',
            true,
            0.1,
            [],
        ));
        $mismatchedResult = (new BrowserAcceptanceExecutor($mismatchedIdentity))->execute($request);

        $this->assertSame('browser_execution_failed', $unexpectedResult->errorCode);
        $this->assertSame('unsafe_executor_result', $unsafeResult->errorCode);
        $this->assertSame('unsafe_executor_result', $mismatchedResult->errorCode);
        $this->assertStringNotContainsString('sensitive-unexpected', serialize($unexpectedResult));
    }

    private function request(): ExecutorRequest
    {
        $identity = new AcceptanceExecutionIdentity('app', 'component', 'suite', 'scenario', 'variant');
        $scenario = new class implements AcceptanceScenario
        {
            public function key(): string
            {
                return 'scenario';
            }

            public function name(): string
            {
                return 'Safe scenario';
            }

            public function metadata(): ScenarioMetadata
            {
                return new ScenarioMetadata(
                    capabilities: ['browser'],
                    tags: ['unit'],
                    disposition: AutomationDisposition::AUTOMATED,
                    evidenceMode: EvidenceMode::METADATA_ONLY,
                );
            }

            public function steps(TestContext $context): iterable
            {
                return [];
            }
        };

        return ExecutorRequest::browser($identity, $scenario, new RunOptions);
    }
}
