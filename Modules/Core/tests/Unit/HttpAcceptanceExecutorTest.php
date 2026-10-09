<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Modules\Core\Contracts\HttpResponseNormalizer;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\ExecutorRequest;
use Modules\Core\Data\HttpExecutorData;
use Modules\Core\Data\HttpExecutorRequest;
use Modules\Core\Enums\ExecutorResultStatus;
use Modules\Core\Services\HttpAcceptanceExecutor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HttpAcceptanceExecutorTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $capturedOptions = [];

    #[DataProvider('methodProvider')]
    public function test_it_dispatches_each_allowed_method_once_with_safe_transport_policy(string $method): void
    {
        $factory = new Factory;
        $factory->preventStrayRequests();
        $factory->fake([
            'https://example.test/check*' => function (Request $request, array $options) {
                $this->capturedOptions = $options;

                return Factory::response('response-body', 202, ['X-Safe' => 'yes']);
            },
        ]);
        $normalizer = new class implements HttpResponseNormalizer
        {
            public int $calls = 0;

            public function normalize(int $statusCode, array $headers, string $body): HttpExecutorData
            {
                $this->calls++;

                return new HttpExecutorData($statusCode, ['body_length' => strlen($body)]);
            }
        };
        $request = $this->request($normalizer, method: $method, body: 'request-body');

        $result = (new HttpAcceptanceExecutor($factory))->execute($request);

        $this->assertSame(ExecutorResultStatus::Succeeded, $result->status);
        $this->assertSame(1, $normalizer->calls);
        $this->assertFalse($this->capturedOptions['allow_redirects']);
        $this->assertTrue($this->capturedOptions['verify']);
        $this->assertSame(3, $this->capturedOptions['connect_timeout']);
        $this->assertSame(10, $this->capturedOptions['timeout']);
        $factory->assertSentCount(1);
        $factory->assertSent(fn (Request $sent): bool => $sent->method() === $method
            && $sent->url() === 'https://example.test/check?probe=1'
            && $sent->hasHeader('X-Safe', 'yes')
            && $sent->body() === 'request-body');
    }

    #[DataProvider('statusProvider')]
    public function test_it_delivers_http_statuses_to_the_normalizer_without_throwing(int $status): void
    {
        $factory = new Factory;
        $factory->preventStrayRequests()->fake([
            'https://example.test/*' => Factory::response('safe-body', $status, ['X-Safe' => 'yes']),
        ]);
        $normalizer = new class implements HttpResponseNormalizer
        {
            public ?int $receivedStatus = null;

            public function normalize(int $statusCode, array $headers, string $body): HttpExecutorData
            {
                $this->receivedStatus = $statusCode;

                return new HttpExecutorData($statusCode, ['received' => true]);
            }
        };

        $result = (new HttpAcceptanceExecutor($factory))->execute($this->request($normalizer));

        $this->assertSame(ExecutorResultStatus::Succeeded, $result->status);
        $this->assertSame($status, $normalizer->receivedStatus);
        $factory->assertSentCount(1);
    }

    public function test_it_normalizes_connection_failures_without_retry_or_leakage(): void
    {
        $factory = new Factory;
        $factory->preventStrayRequests()->fake([
            'https://example.test/*' => Factory::failedConnection('sensitive-transport-error'),
        ]);
        $normalizer = $this->normalizer();

        $result = (new HttpAcceptanceExecutor($factory))->execute($this->request($normalizer));

        $this->assertSame('http_transport_failed', $result->errorCode);
        $this->assertTrue($result->retryable);
        $this->assertFalse($result->permanent);
        $this->assertStringNotContainsString('sensitive-transport-error', serialize($result));
        $factory->assertSentCount(1);
    }

    public function test_it_rejects_oversize_and_unsafe_normalizer_output(): void
    {
        $factory = new Factory;
        $factory->preventStrayRequests()->fake([
            'https://example.test/*' => Factory::response('oversize', 200),
        ]);
        $normalizer = new class implements HttpResponseNormalizer
        {
            public int $calls = 0;

            public function normalize(int $statusCode, array $headers, string $body): HttpExecutorData
            {
                $this->calls++;

                return new HttpExecutorData(201, ['safe' => true]);
            }
        };
        $request = $this->request($normalizer, maxResponseBytes: 4);

        $oversize = (new HttpAcceptanceExecutor($factory))->execute($request);

        $this->assertSame('unsafe_executor_result', $oversize->errorCode);
        $this->assertSame(0, $normalizer->calls);

        $secondFactory = new Factory;
        $secondFactory->preventStrayRequests()->fake([
            'https://example.test/*' => Factory::response('safe', 200),
        ]);
        $unsafe = (new HttpAcceptanceExecutor($secondFactory))->execute($this->request($normalizer));

        $this->assertSame('unsafe_executor_result', $unsafe->errorCode);
        $this->assertSame(1, $normalizer->calls);
    }

    public function test_it_contains_normalizer_exceptions(): void
    {
        $factory = new Factory;
        $factory->preventStrayRequests()->fake([
            'https://example.test/*' => Factory::response('safe', 200),
        ]);
        $normalizer = new class implements HttpResponseNormalizer
        {
            public function normalize(int $statusCode, array $headers, string $body): HttpExecutorData
            {
                throw new RuntimeException('sensitive-normalizer-error');
            }
        };

        $result = (new HttpAcceptanceExecutor($factory))->execute($this->request($normalizer));

        $this->assertSame('unsafe_executor_result', $result->errorCode);
        $this->assertStringNotContainsString('sensitive-normalizer-error', serialize($result));
    }

    /** @return array<string, array{string}> */
    public static function methodProvider(): array
    {
        return array_combine(
            ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            array_map(static fn (string $method): array => [$method], ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']),
        );
    }

    /** @return array<string, array{int}> */
    public static function statusProvider(): array
    {
        return [
            'informational' => [199],
            'successful' => [204],
            'client failure' => [418],
            'server failure' => [503],
        ];
    }

    private function normalizer(): HttpResponseNormalizer
    {
        return new class implements HttpResponseNormalizer
        {
            public function normalize(int $statusCode, array $headers, string $body): HttpExecutorData
            {
                return new HttpExecutorData($statusCode, ['safe' => true]);
            }
        };
    }

    private function request(
        HttpResponseNormalizer $normalizer,
        string $method = 'GET',
        ?string $body = null,
        int $maxResponseBytes = HttpExecutorRequest::MAX_BODY_BYTES,
    ): ExecutorRequest {
        return ExecutorRequest::http(
            new AcceptanceExecutionIdentity('app', 'component', 'suite', 'scenario', 'variant'),
            new HttpExecutorRequest(
                method: $method,
                url: 'https://example.test/check',
                headers: ['X-Safe' => 'yes'],
                query: ['probe' => 1],
                body: $body,
                contentType: 'application/json',
                normalizer: $normalizer,
                maxResponseBytes: $maxResponseBytes,
            ),
        );
    }
}
