<?php

namespace Tests\Unit\Acceptance\Targets\Control;

use App\Acceptance\Targets\Control\Contracts\SecretHandleResolver;
use App\Acceptance\Targets\Control\Data\TargetControlCredential;
use App\Acceptance\Targets\Control\Data\TargetControlLeaseRequest;
use App\Acceptance\Targets\Control\Exceptions\TargetControlException;
use App\Acceptance\Targets\Control\Http\HttpTargetControlClient;
use App\Acceptance\Targets\Control\TargetControlProtocol;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HttpTargetControlClientTest extends TestCase
{
    public function test_acquire_negotiates_capabilities_and_sends_the_exact_secure_request(): void
    {
        $factory = new Factory;
        $factory->preventStrayRequests();
        $request = $this->request();
        $capturedOptions = [];
        $factory->fake(function (Request $sent, array $options) use (&$capturedOptions, $request) {
            $capturedOptions[] = $options;

            return str_ends_with($sent->url(), TargetControlProtocol::CAPABILITIES_PATH)
                ? Factory::response($this->capabilities(), 200, ['Content-Type' => 'application/json'])
                : Factory::response($this->lease($request), 201, ['Content-Type' => 'application/json']);
        });
        $resolver = new RecordingSecretHandleResolver;
        $client = new HttpTargetControlClient($factory, $resolver);

        $result = $client->acquireLease(
            'https://target.invalid',
            'app-secret://app-a/opaque-handle-a',
            $request,
        );

        $this->assertSame('active', $result->state);
        $this->assertSame(['ready' => true], $result->facts);
        $this->assertSame(2, $resolver->calls);
        $this->assertSame([false, false], array_column($capturedOptions, 'allow_redirects'));
        $this->assertSame([true, true], array_column($capturedOptions, 'verify'));
        $this->assertSame(['', ''], array_column($capturedOptions, 'proxy'));
        $this->assertSame([3, 3], array_column($capturedOptions, 'connect_timeout'));
        $this->assertSame([10, 10], array_column($capturedOptions, 'timeout'));
        $factory->assertSentCount(2);
        $factory->assertSent(function (Request $sent) use ($request): bool {
            if ($sent->method() !== 'PUT') {
                return true;
            }

            return $sent->url() === 'https://target.invalid'.TargetControlProtocol::leasePath($request->lifecycleId)
                && $sent->hasHeader('Authorization', 'Bearer inert-credential-value')
                && $sent->hasHeader(TargetControlProtocol::HEADER_PROTOCOL, TargetControlProtocol::IDENTITY)
                && $sent->hasHeader(TargetControlProtocol::HEADER_CORRELATION_ID, $request->correlationId)
                && $sent->hasHeader(TargetControlProtocol::HEADER_REQUEST_DIGEST, $request->requestDigest)
                && $sent->hasHeader(TargetControlProtocol::HEADER_IDEMPOTENCY_KEY, $request->requestDigest)
                && $sent->data() === $request->payload()
                && ! str_contains($sent->body(), 'opaque-handle-a')
                && ! str_contains($sent->body(), 'inert-credential-value');
        });
    }

    public function test_inspect_and_cleanup_map_bounded_results_without_recipe_revalidation_on_cleanup(): void
    {
        $factory = new Factory;
        $factory->preventStrayRequests();
        $request = $this->request();
        $factory->fake(function (Request $sent) use ($request) {
            if (str_ends_with($sent->url(), TargetControlProtocol::CAPABILITIES_PATH)) {
                $capabilities = $this->capabilities();
                $capabilities['components'][0]['recipes'] = ['replacement-recipe.v1'];

                return Factory::response($capabilities, 200, ['Content-Type' => 'application/json']);
            }

            $state = $sent->method() === 'DELETE' ? 'cleaned' : 'active';

            return Factory::response($this->lease($request, $state), 200, ['Content-Type' => 'application/json']);
        });
        $client = new HttpTargetControlClient($factory, new RecordingSecretHandleResolver);

        $inspected = $client->inspectLease('https://target.invalid', 'app-secret://app-a/opaque-handle-a', $request);
        $cleaned = $client->cleanupLease('https://target.invalid', 'app-secret://app-a/opaque-handle-a', $request);

        $this->assertSame('active', $inspected->state);
        $this->assertSame('cleaned', $cleaned->state);
        $factory->assertSentCount(3);
    }

    public function test_capability_mismatch_stops_before_mutation(): void
    {
        $factory = new Factory;
        $factory->preventStrayRequests()->fake([
            'https://target.invalid'.TargetControlProtocol::CAPABILITIES_PATH => Factory::response([
                ...$this->capabilities(),
                'components' => [[
                    'key' => 'component-a',
                    'recipes' => ['other-recipe.v1'],
                    'observations' => [],
                ]],
            ], 200, ['Content-Type' => 'application/json']),
        ]);
        $client = new HttpTargetControlClient($factory, new RecordingSecretHandleResolver);

        try {
            $client->acquireLease('https://target.invalid', 'app-secret://app-a/opaque-handle-a', $this->request());
            $this->fail('A missing capability was accepted.');
        } catch (TargetControlException $exception) {
            $this->assertSame('target_control_capability_mismatch', $exception->errorCode);
            $this->assertFalse($exception->retryable);
        }

        $factory->assertSentCount(1);
    }

    public function test_transient_state_changing_response_retries_with_the_same_digest_and_body(): void
    {
        $factory = new Factory;
        $factory->preventStrayRequests();
        $request = $this->request();
        $putAttempts = [];
        $factory->fake(function (Request $sent) use (&$putAttempts, $request) {
            if ($sent->method() === 'GET') {
                return Factory::response($this->capabilities(), 200, ['Content-Type' => 'application/json']);
            }

            $putAttempts[] = [
                'digest' => $sent->header(TargetControlProtocol::HEADER_REQUEST_DIGEST)[0] ?? null,
                'body' => $sent->body(),
            ];

            return count($putAttempts) === 1
                ? Factory::response(
                    json_encode($this->problem('target_control_unavailable', 503, true), JSON_THROW_ON_ERROR),
                    503,
                    ['Content-Type' => 'application/problem+json'],
                )
                : Factory::response($this->lease($request), 200, ['Content-Type' => 'application/json']);
        });
        $resolver = new RecordingSecretHandleResolver;
        $client = new HttpTargetControlClient($factory, $resolver);

        $result = $client->acquireLease('https://target.invalid', 'app-secret://app-a/opaque-handle-a', $request);

        $this->assertSame('active', $result->state);
        $this->assertCount(2, $putAttempts);
        $this->assertSame($putAttempts[0], $putAttempts[1]);
        $this->assertSame($request->requestDigest, $putAttempts[0]['digest']);
        $this->assertSame(3, $resolver->calls);
    }

    public function test_connection_failure_retries_with_fresh_credentials_and_returns_a_safe_error(): void
    {
        $factory = new Factory;
        $factory->preventStrayRequests()->fake([
            'https://target.invalid/*' => $factory->sequence()
                ->pushFailedConnection('raw-sensitive-network-detail')
                ->pushFailedConnection('raw-sensitive-network-detail'),
        ]);
        $resolver = new RecordingSecretHandleResolver;
        $client = new HttpTargetControlClient($factory, $resolver);

        try {
            $client->inspectLease('https://target.invalid', 'app-secret://app-a/opaque-handle-a', $this->request());
            $this->fail('Connection failure was not normalized.');
        } catch (TargetControlException $exception) {
            $this->assertSame('target_control_unavailable', $exception->errorCode);
            $this->assertTrue($exception->retryable);
            $this->assertStringNotContainsString('raw-sensitive-network-detail', $exception->getMessage());
            $this->assertStringNotContainsString('opaque-handle-a', $exception->getMessage());
        }

        $this->assertSame(2, $resolver->calls);
        $factory->assertSentCount(2);
    }

    #[DataProvider('problemProvider')]
    public function test_problem_responses_map_only_known_codes(
        string $code,
        int $status,
        bool $retryable,
    ): void {
        $factory = new Factory;
        $factory->preventStrayRequests()->fake([
            'https://target.invalid/*' => Factory::response(
                json_encode($this->problem($code, $status, $retryable), JSON_THROW_ON_ERROR),
                $status,
                ['Content-Type' => 'application/problem+json'],
            ),
        ]);
        $client = new HttpTargetControlClient($factory, new RecordingSecretHandleResolver, maxAttempts: 1);

        try {
            $client->inspectLease('https://target.invalid', 'app-secret://app-a/opaque-handle-a', $this->request());
            $this->fail('Problem response was not mapped.');
        } catch (TargetControlException $exception) {
            $this->assertSame($code, $exception->errorCode);
            $this->assertSame($status, $exception->httpStatus);
            $this->assertSame($retryable, $exception->retryable);
        }
    }

    public static function problemProvider(): iterable
    {
        yield 'unauthorized' => ['target_control_unauthorized', 401, false];
        yield 'capability mismatch' => ['target_control_capability_mismatch', 412, false];
        yield 'lease conflict' => ['target_control_lease_conflict', 409, false];
        yield 'recipe rejected' => ['target_control_recipe_rejected', 422, false];
        yield 'cleanup failed' => ['target_control_cleanup_failed', 500, true];
        yield 'unavailable' => ['target_control_unavailable', 503, true];
        yield 'protocol invalid' => ['target_control_protocol_invalid', 400, false];
    }

    #[DataProvider('invalidResponseProvider')]
    public function test_malformed_unsafe_redirected_and_oversized_responses_fail_closed(
        int $status,
        string $body,
        string $contentType,
    ): void {
        $factory = new Factory;
        $factory->preventStrayRequests()->fake([
            'https://target.invalid/*' => Factory::response(
                $body,
                $status,
                ['Content-Type' => $contentType, 'Location' => 'https://other.invalid'],
            ),
        ]);
        $client = new HttpTargetControlClient(
            $factory,
            new RecordingSecretHandleResolver,
            maxAttempts: 1,
            maxResponseBytes: 1024,
        );

        $this->expectException(TargetControlException::class);
        $this->expectExceptionMessage('target_control_protocol_invalid');

        $client->inspectLease('https://target.invalid', 'app-secret://app-a/opaque-handle-a', $this->request());
    }

    public static function invalidResponseProvider(): iterable
    {
        yield 'malformed json' => [200, '{', 'application/json'];
        yield 'wrong content type' => [200, '{}', 'text/plain'];
        yield 'redirect' => [302, '{}', 'application/json'];
        yield 'oversized' => [200, str_repeat('a', 1025), 'application/json'];
        yield 'unknown fields' => [200, json_encode([
            'protocol' => 'acceptance-control.v1',
            'lifecycle_id' => 'dcb1cf9d-207c-4a44-963b-000000000001',
            'request_digest' => str_repeat('a', 64),
            'state' => 'active',
            'references' => [],
            'lease_context' => null,
            'observation' => null,
            'raw' => 'forbidden',
        ], JSON_THROW_ON_ERROR), 'application/json'];
    }

    public function test_invalid_endpoint_handle_and_resolver_failure_never_send_or_leak(): void
    {
        $factory = new Factory;
        $factory->preventStrayRequests();
        $request = $this->request();
        $client = new HttpTargetControlClient($factory, new RecordingSecretHandleResolver);

        foreach ([
            ['http://target.invalid', 'app-secret://app-a/opaque-handle-a'],
            ['https://target.invalid/path', 'app-secret://app-a/opaque-handle-a'],
            ['https://target.invalid', 'app-secret://other-app/opaque-handle-a'],
        ] as [$endpoint, $handle]) {
            try {
                $client->inspectLease($endpoint, $handle, $request);
                $this->fail('Unsafe endpoint or handle was accepted.');
            } catch (TargetControlException $exception) {
                $this->assertSame('target_control_protocol_invalid', $exception->errorCode);
                $this->assertStringNotContainsString($handle, $exception->getMessage());
            }
        }

        $failing = new HttpTargetControlClient($factory, new ThrowingSecretHandleResolver);
        try {
            $failing->inspectLease('https://target.invalid', 'app-secret://app-a/opaque-handle-a', $request);
            $this->fail('Resolver failure was not normalized.');
        } catch (TargetControlException $exception) {
            $this->assertSame('target_control_unavailable', $exception->errorCode);
            $this->assertStringNotContainsString('raw-sensitive-resolver-detail', $exception->getMessage());
        }

        $factory->assertNothingSent();
    }

    private function request(): TargetControlLeaseRequest
    {
        return new TargetControlLeaseRequest(
            'dcb1cf9d-207c-4a44-963b-000000000001',
            'dcb1cf9d-207c-4a44-963b-000000000002',
            'app-a',
            'component-a',
            'recipe-a.v1',
            '2030-01-02T03:04:05Z',
            300,
        );
    }

    /** @return array<string, mixed> */
    private function capabilities(): array
    {
        return [
            'protocol' => TargetControlProtocol::IDENTITY,
            'server_version' => 'server-v1',
            'max_lease_ttl_seconds' => 600,
            'components' => [[
                'key' => 'component-a',
                'recipes' => ['recipe-a.v1'],
                'observations' => ['observation-a.v1'],
            ]],
        ];
    }

    /** @return array<string, mixed> */
    private function lease(TargetControlLeaseRequest $request, string $state = 'active'): array
    {
        return [
            'protocol' => TargetControlProtocol::IDENTITY,
            'lifecycle_id' => $request->lifecycleId,
            'request_digest' => $request->requestDigest,
            'state' => $state,
            'references' => [[
                'type' => 'fixture',
                'id' => 'opaque-fixture-a',
                'version' => 1,
            ]],
            'lease_context' => [
                'type' => 'lease-context',
                'id' => 'opaque-context-a',
                'version' => 1,
            ],
            'observation' => [
                'key' => 'observation-a.v1',
                'facts' => ['ready' => true],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function problem(string $code, int $status, bool $retryable): array
    {
        return [
            'type' => 'urn:tms:acceptance-control:error:'.$code,
            'title' => 'Acceptance control request failed.',
            'status' => $status,
            'code' => $code,
            'retryable' => $retryable,
        ];
    }
}

final class RecordingSecretHandleResolver implements SecretHandleResolver
{
    public int $calls = 0;

    public function resolve(string $appKey, string $secretHandle): TargetControlCredential
    {
        $this->calls++;

        return new TargetControlCredential('inert-credential-value');
    }
}

final class ThrowingSecretHandleResolver implements SecretHandleResolver
{
    public function resolve(string $appKey, string $secretHandle): TargetControlCredential
    {
        throw new RuntimeException('raw-sensitive-resolver-detail');
    }
}
