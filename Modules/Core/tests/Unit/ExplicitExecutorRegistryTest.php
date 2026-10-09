<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use Closure;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Modules\Core\Contracts\AcceptanceExecutor;
use Modules\Core\Contracts\HttpResponseNormalizer;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\ExecutorCapability;
use Modules\Core\Data\ExecutorRequest;
use Modules\Core\Data\ExecutorResult;
use Modules\Core\Data\ExecutorTraceContext;
use Modules\Core\Data\HttpExecutorData;
use Modules\Core\Data\HttpExecutorRequest;
use Modules\Core\Enums\ExecutorResultStatus;
use Modules\Core\Exceptions\ExecutorException;
use Modules\Core\Services\ExplicitExecutorRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class ExplicitExecutorRegistryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_preserves_registration_order_and_routes_by_capability(): void
    {
        $browser = $this->executor('browser-executor', ExecutorCapability::browser());
        $http = $this->executor(
            'http-executor',
            ExecutorCapability::http(),
            fn (ExecutorRequest $request): ExecutorResult => ExecutorResult::succeeded(
                $request,
                'http-executor',
                new HttpExecutorData(204, ['reachable' => true]),
            ),
        );
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')
            ->once()
            ->with('tms.core.executor.completed', Mockery::on(fn (array $context): bool => $context['correlation_id'] === '123e4567-e89b-12d3-a456-426614174000'));
        $registry = new ExplicitExecutorRegistry([$browser, $http], $logger);
        $request = $this->request(new ExecutorTraceContext(
            correlationId: '123e4567-e89b-12d3-a456-426614174000',
            batchId: 9,
        ));

        $result = $registry->execute($request);

        $this->assertSame($browser, $registry->for(ExecutorCapability::browser()));
        $this->assertSame($http, $registry->for(ExecutorCapability::http()));
        $this->assertSame(ExecutorResultStatus::Succeeded, $result->status);
        $this->assertSame($request->identity, $result->identity);
        $this->assertSame($request->trace, $result->trace);
    }

    public function test_it_rejects_duplicate_executor_keys_and_capability_owners(): void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $first = $this->executor('first', ExecutorCapability::http());

        foreach ([
            $this->executor('first', ExecutorCapability::browser()),
            $this->executor('second', ExecutorCapability::http()),
        ] as $duplicate) {
            try {
                new ExplicitExecutorRegistry([$first, $duplicate], $logger);
                $this->fail('Expected duplicate registration to fail.');
            } catch (ExecutorException $exception) {
                $this->assertSame('executor_registry_invalid', $exception->errorCode);
            }
        }
    }

    public function test_missing_capability_returns_typed_unsupported_result_and_logs_once(): void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('warning')
            ->once()
            ->with('tms.core.executor.unsupported', Mockery::type('array'));
        $registry = new ExplicitExecutorRegistry([], $logger);

        $result = $registry->execute($this->request(authorization: 'sensitive-value'));

        $this->assertSame(ExecutorResultStatus::Unsupported, $result->status);
        $this->assertSame('executor_not_found', $result->errorCode);
        $this->assertTrue($result->adminActionRequired);
    }

    public function test_it_contains_mismatched_executor_results_and_logs_safe_failure(): void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('error')
            ->once()
            ->with('tms.core.executor.failed', Mockery::on(function (array $context): bool {
                $serialized = serialize($context);

                return $context['error_code'] === 'unsafe_executor_result'
                    && ! str_contains($serialized, 'sensitive-value');
            }));
        $otherRequest = $this->request(new ExecutorTraceContext(testId: 99));
        $executor = $this->executor(
            'http-executor',
            ExecutorCapability::http(),
            fn (): ExecutorResult => ExecutorResult::succeeded(
                $otherRequest,
                'http-executor',
                new HttpExecutorData(200, ['safe' => true]),
            ),
        );
        $registry = new ExplicitExecutorRegistry([$executor], $logger);

        $result = $registry->execute($this->request(authorization: 'sensitive-value'));

        $this->assertSame('unsafe_executor_result', $result->errorCode);
        $this->assertTrue($result->adminActionRequired);
    }

    private function executor(
        string $key,
        ExecutorCapability $capability,
        ?Closure $execute = null,
    ): AcceptanceExecutor {
        return new class($key, $capability, $execute) implements AcceptanceExecutor
        {
            public function __construct(
                private readonly string $executorKey,
                private readonly ExecutorCapability $capability,
                private readonly ?Closure $execute,
            ) {}

            public function key(): string
            {
                return $this->executorKey;
            }

            public function capabilities(): array
            {
                return [$this->capability];
            }

            public function execute(ExecutorRequest $request): ExecutorResult
            {
                return ($this->execute)($request);
            }
        };
    }

    private function request(
        ?ExecutorTraceContext $trace = null,
        string $authorization = 'safe-placeholder',
    ): ExecutorRequest {
        $normalizer = new class implements HttpResponseNormalizer
        {
            public function normalize(int $statusCode, array $headers, string $body): HttpExecutorData
            {
                return new HttpExecutorData($statusCode, []);
            }
        };

        return ExecutorRequest::http(
            new AcceptanceExecutionIdentity('app', 'component', 'suite', 'scenario', 'variant'),
            new HttpExecutorRequest(
                'GET',
                'https://example.test/status',
                ['Authorization' => $authorization],
                [],
                null,
                'application/json',
                $normalizer,
            ),
            $trace,
        );
    }
}
