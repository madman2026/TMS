<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use InvalidArgumentException;
use Modules\Core\Contracts\HttpResponseNormalizer;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\BrowserStepData;
use Modules\Core\Data\ExecutorCapability;
use Modules\Core\Data\ExecutorRequest;
use Modules\Core\Data\ExecutorResult;
use Modules\Core\Data\ExecutorTraceContext;
use Modules\Core\Data\HttpExecutorData;
use Modules\Core\Data\HttpExecutorRequest;
use Modules\Core\Exceptions\ExecutorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExecutorResultSafetyTest extends TestCase
{
    #[DataProvider('invalidCapabilityProvider')]
    public function test_capabilities_reject_invalid_keys(string $key): void
    {
        $this->expectException(ExecutorException::class);

        new ExecutorCapability($key);
    }

    public function test_trace_rejects_invalid_identifiers_and_preserves_only_non_null_context(): void
    {
        $trace = new ExecutorTraceContext(
            correlationId: '123e4567-e89b-12d3-a456-426614174000',
            itemId: 7,
        );

        $this->assertSame([
            'correlation_id' => '123e4567-e89b-12d3-a456-426614174000',
            'item_id' => 7,
        ], $trace->logContext());

        $this->expectException(ExecutorException::class);
        new ExecutorTraceContext(operationId: 'not-a-uuid', batchId: 0);
    }

    public function test_http_request_rejects_unsafe_target_data_and_copies_input_arrays(): void
    {
        $headers = ['X-Safe' => 'yes'];
        $query = ['probe' => 1];
        $request = new HttpExecutorRequest(
            'POST',
            'https://example.test/check',
            $headers,
            $query,
            '{}',
            'application/json',
            $this->normalizer(),
        );
        $headers['X-Safe'] = 'mutated';
        $query['probe'] = 2;

        $this->assertSame('yes', $request->headers['X-Safe']);
        $this->assertSame(1, $request->query['probe']);

        foreach ([
            ['method' => 'TRACE', 'url' => 'https://example.test'],
            ['method' => 'GET', 'url' => 'https://user:pass@example.test'],
            ['method' => 'GET', 'url' => 'https://example.test/path#fragment'],
            ['method' => 'GET', 'url' => 'ftp://example.test/path'],
        ] as $invalid) {
            try {
                new HttpExecutorRequest(
                    $invalid['method'],
                    $invalid['url'],
                    [],
                    [],
                    null,
                    'application/json',
                    $this->normalizer(),
                );
                $this->fail('Expected invalid HTTP request to fail.');
            } catch (ExecutorException $exception) {
                $this->assertSame('executor_request_invalid', $exception->errorCode);
            }
        }
    }

    public function test_http_request_rejects_invalid_bounds_and_header_or_query_shapes(): void
    {
        $cases = [
            ['connectTimeoutMs' => 0],
            ['connectTimeoutMs' => 4_000, 'timeoutMs' => 3_000],
            ['timeoutMs' => 60_001],
            ['maxResponseBytes' => 0],
            ['headers' => ['Bad Header' => 'value']],
            ['headers' => ['X-Safe' => "value\r\ninjected"]],
            ['query' => ['probe' => ['nested']]],
            ['body' => str_repeat('x', HttpExecutorRequest::MAX_BODY_BYTES + 1)],
        ];

        foreach ($cases as $overrides) {
            $arguments = array_replace([
                'method' => 'GET',
                'url' => 'https://example.test/check',
                'headers' => [],
                'query' => [],
                'body' => null,
                'contentType' => 'application/json',
                'normalizer' => $this->normalizer(),
                'connectTimeoutMs' => HttpExecutorRequest::DEFAULT_CONNECT_TIMEOUT_MS,
                'timeoutMs' => HttpExecutorRequest::DEFAULT_TIMEOUT_MS,
                'maxResponseBytes' => HttpExecutorRequest::MAX_BODY_BYTES,
            ], $overrides);

            try {
                new HttpExecutorRequest(...$arguments);
                $this->fail('Expected invalid HTTP bounds or shape to fail.');
            } catch (ExecutorException $exception) {
                $this->assertSame('executor_request_invalid', $exception->errorCode);
            }
        }
    }

    public function test_http_observations_reject_nested_non_finite_or_unsafe_values_and_copy_input(): void
    {
        $observations = ['safe' => 'value'];
        $data = new HttpExecutorData(200, $observations);
        $observations['safe'] = 'mutated';

        $this->assertSame('value', $data->observations['safe']);

        foreach ([
            ['nested' => ['unsafe']],
            ['infinite' => INF],
            ['control' => "unsafe\0value"],
            ['oversize' => str_repeat('x', 1025)],
        ] as $invalid) {
            try {
                new HttpExecutorData(200, $invalid);
                $this->fail('Expected unsafe observation to fail.');
            } catch (ExecutorException $exception) {
                $this->assertSame('unsafe_executor_result', $exception->errorCode);
            }
        }
    }

    public function test_browser_step_rejects_invalid_utf8_control_text_duration_and_error_code(): void
    {
        foreach ([
            ["unsafe\0name", 0, null],
            ["\xB1\x31", 0, null],
            ['safe', -1, null],
            ['safe', 0, 'Invalid Code'],
        ] as [$name, $duration, $errorCode]) {
            try {
                new BrowserStepData(1, $name, false, $errorCode, $duration, true);
                $this->fail('Expected unsafe Browser step to fail.');
            } catch (ExecutorException $exception) {
                $this->assertSame('unsafe_executor_result', $exception->errorCode);
            }
        }
    }

    public function test_result_classifications_are_consistent_and_omit_request_secrets(): void
    {
        $request = $this->request('authorization-secret');
        $result = ExecutorResult::failed(
            $request,
            'http',
            'http_transport_failed',
            true,
            false,
            false,
        );

        $this->assertStringNotContainsString('authorization-secret', serialize($result));

        $this->expectException(ExecutorException::class);
        ExecutorResult::failed(
            $request,
            'http',
            'executor_not_found',
            false,
            true,
            true,
        );
    }

    public function test_executor_requests_cannot_be_serialized(): void
    {
        $request = $this->request('serialization-secret');

        foreach ([
            static fn (): string => serialize($request),
            static fn (): string|false => json_encode($request),
            static fn (): string => serialize($request->http),
            static fn (): string|false => json_encode($request->http),
        ] as $serialize) {
            try {
                $serialize();
                $this->fail('Expected transient executor request serialization to fail.');
            } catch (ExecutorException $exception) {
                $this->assertSame('executor_request_invalid', $exception->errorCode);
                $this->assertStringNotContainsString('serialization-secret', $exception->getMessage());
            }
        }
    }

    public function test_identity_still_rejects_an_invalid_hierarchy_tuple(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AcceptanceExecutionIdentity('Invalid Key', 'component', 'suite', 'scenario', 'variant');
    }

    /** @return array<string, array{string}> */
    public static function invalidCapabilityProvider(): array
    {
        return [
            'empty' => [''],
            'uppercase' => ['Browser'],
            'space' => ['invalid key'],
            'oversize' => [str_repeat('x', 65)],
        ];
    }

    private function request(string $authorization): ExecutorRequest
    {
        return ExecutorRequest::http(
            new AcceptanceExecutionIdentity('app', 'component', 'suite', 'scenario', 'variant'),
            new HttpExecutorRequest(
                'GET',
                'https://example.test/check',
                ['Authorization' => $authorization],
                [],
                null,
                'application/json',
                $this->normalizer(),
            ),
        );
    }

    private function normalizer(): HttpResponseNormalizer
    {
        return new class implements HttpResponseNormalizer
        {
            public function normalize(int $statusCode, array $headers, string $body): HttpExecutorData
            {
                return new HttpExecutorData($statusCode, []);
            }
        };
    }
}
