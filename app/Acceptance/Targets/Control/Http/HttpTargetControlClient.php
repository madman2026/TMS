<?php

namespace App\Acceptance\Targets\Control\Http;

use App\Acceptance\Targets\Control\Contracts\SecretHandleResolver;
use App\Acceptance\Targets\Control\Contracts\TargetControlClient;
use App\Acceptance\Targets\Control\Data\TargetControlCapabilities;
use App\Acceptance\Targets\Control\Data\TargetControlLeaseRequest;
use App\Acceptance\Targets\Control\Data\TargetControlLeaseResult;
use App\Acceptance\Targets\Control\Exceptions\TargetControlException;
use App\Acceptance\Targets\Control\TargetControlProtocol;
use App\Acceptance\Targets\Data\ResourceReference;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final readonly class HttpTargetControlClient implements TargetControlClient
{
    public function __construct(
        private Factory $http,
        private SecretHandleResolver $secretResolver,
        private int $connectTimeoutSeconds = 3,
        private int $timeoutSeconds = 10,
        private int $maxAttempts = 2,
        private int $maxResponseBytes = TargetControlProtocol::MAX_RESPONSE_BYTES,
        private bool $allowLocalLoopbackHttp = false,
    ) {
        if ($connectTimeoutSeconds < 1 || $connectTimeoutSeconds > 10
            || $timeoutSeconds < $connectTimeoutSeconds || $timeoutSeconds > 30
            || $maxAttempts < 1 || $maxAttempts > TargetControlProtocol::MAX_ATTEMPTS
            || $maxResponseBytes < 1024
            || $maxResponseBytes > TargetControlProtocol::MAX_RESPONSE_BYTES) {
            throw new InvalidArgumentException('target_control_client_configuration_invalid');
        }
    }

    public function capabilities(
        string $endpoint,
        string $secretHandle,
        TargetControlLeaseRequest $request,
    ): TargetControlCapabilities {
        $response = $this->sendWithRetries(
            'GET',
            $endpoint,
            TargetControlProtocol::CAPABILITIES_PATH,
            $secretHandle,
            $request,
        );
        $payload = $this->successfulPayload($response, [200]);

        return $this->mapCapabilities($payload);
    }

    public function acquireLease(
        string $endpoint,
        string $secretHandle,
        TargetControlLeaseRequest $request,
    ): TargetControlLeaseResult {
        $capabilities = $this->capabilities($endpoint, $secretHandle, $request);
        if (! $capabilities->supports($request->componentKey, $request->recipeKey)
            || $request->leaseTtlSeconds > $capabilities->maxLeaseTtlSeconds) {
            throw TargetControlException::because('target_control_capability_mismatch');
        }

        $response = $this->sendWithRetries(
            'PUT',
            $endpoint,
            TargetControlProtocol::leasePath($request->lifecycleId),
            $secretHandle,
            $request,
            $request->payload(),
        );
        $result = $this->mapLeaseResult($this->successfulPayload($response, [200, 201]), $request);
        if ($result->state !== 'active') {
            throw TargetControlException::because('target_control_protocol_invalid');
        }

        return $result;
    }

    public function inspectLease(
        string $endpoint,
        string $secretHandle,
        TargetControlLeaseRequest $request,
    ): TargetControlLeaseResult {
        $response = $this->sendWithRetries(
            'GET',
            $endpoint,
            TargetControlProtocol::leasePath($request->lifecycleId),
            $secretHandle,
            $request,
        );

        return $this->mapLeaseResult($this->successfulPayload($response, [200]), $request);
    }

    public function cleanupLease(
        string $endpoint,
        string $secretHandle,
        TargetControlLeaseRequest $request,
    ): TargetControlLeaseResult {
        // Cleanup still negotiates the protocol version, but it must remain possible
        // after a recipe is removed from the current capability advertisement.
        $this->capabilities($endpoint, $secretHandle, $request);

        $response = $this->sendWithRetries(
            'DELETE',
            $endpoint,
            TargetControlProtocol::leasePath($request->lifecycleId),
            $secretHandle,
            $request,
            $request->payload(),
        );
        $result = $this->mapLeaseResult($this->successfulPayload($response, [200]), $request);
        if ($result->state !== 'cleaned') {
            throw TargetControlException::because('target_control_cleanup_failed');
        }

        return $result;
    }

    /** @param null|array<string, int|string> $payload */
    private function sendWithRetries(
        string $method,
        string $endpoint,
        string $path,
        string $secretHandle,
        TargetControlLeaseRequest $request,
        ?array $payload = null,
    ): Response {
        $origin = $this->validatedOrigin($endpoint);
        $this->assertSecretHandle($secretHandle, $request->appKey);

        for ($attempt = 1; $attempt <= $this->maxAttempts; $attempt++) {
            try {
                $response = $this->sendOnce(
                    $method,
                    $origin.$path,
                    $secretHandle,
                    $request,
                    $payload,
                );
            } catch (ConnectionException) {
                if ($attempt < $this->maxAttempts) {
                    continue;
                }

                throw TargetControlException::because('target_control_unavailable');
            } catch (TargetControlException $exception) {
                throw $exception;
            } catch (Throwable) {
                throw TargetControlException::because('target_control_unavailable');
            }

            if (in_array($response->status(), [502, 503, 504], true) && $attempt < $this->maxAttempts) {
                continue;
            }

            return $response;
        }

        throw TargetControlException::because('target_control_unavailable');
    }

    /** @param null|array<string, int|string> $payload */
    private function sendOnce(
        string $method,
        string $url,
        string $secretHandle,
        TargetControlLeaseRequest $request,
        ?array $payload,
    ): Response {
        try {
            $credential = $this->secretResolver->resolve($request->appKey, $secretHandle);
        } catch (Throwable) {
            throw TargetControlException::because('target_control_unavailable');
        }

        try {
            return $credential->withValue(function (string $value) use ($method, $url, $request, $payload): Response {
                $pending = $this->pendingRequest($request)->withToken($value);

                return $pending->send($method, $url, $payload === null ? [] : ['json' => $payload]);
            });
        } finally {
            unset($credential);
        }
    }

    private function pendingRequest(TargetControlLeaseRequest $request): PendingRequest
    {
        return $this->http
            ->acceptJson()
            ->asJson()
            ->connectTimeout($this->connectTimeoutSeconds)
            ->timeout($this->timeoutSeconds)
            ->withHeaders([
                TargetControlProtocol::HEADER_PROTOCOL => TargetControlProtocol::IDENTITY,
                TargetControlProtocol::HEADER_CORRELATION_ID => $request->correlationId,
                TargetControlProtocol::HEADER_REQUEST_DIGEST => $request->requestDigest,
                TargetControlProtocol::HEADER_IDEMPOTENCY_KEY => $request->requestDigest,
            ])
            ->withOptions([
                'allow_redirects' => false,
                'cookies' => false,
                'http_errors' => false,
                'proxy' => '',
                'verify' => true,
                'on_headers' => function (ResponseInterface $response): void {
                    $length = $response->getHeaderLine('Content-Length');
                    if ($length !== '' && ctype_digit($length) && (int) $length > $this->maxResponseBytes) {
                        throw new InvalidArgumentException('target_control_response_too_large');
                    }
                },
            ]);
    }

    /** @param list<int> $expectedStatuses @return array<string, mixed> */
    private function successfulPayload(Response $response, array $expectedStatuses): array
    {
        if (! in_array($response->status(), $expectedStatuses, true)) {
            throw $this->problemException($response);
        }

        return $this->decodeJson($response, 'application/json');
    }

    private function problemException(Response $response): TargetControlException
    {
        $payload = $this->decodeJson($response, 'application/problem+json');
        $this->assertExactKeys($payload, ['type', 'title', 'status', 'code', 'retryable']);

        $code = $payload['code'] ?? null;
        $status = $payload['status'] ?? null;
        $retryable = $payload['retryable'] ?? null;
        $title = $payload['title'] ?? null;
        $type = $payload['type'] ?? null;
        if (! is_string($code)
            || ! in_array($code, TargetControlProtocol::ERROR_CODES, true)
            || ! is_int($status)
            || $status !== $response->status()
            || ! is_bool($retryable)
            || ! is_string($title)
            || $title === ''
            || strlen($title) > 128
            || ! is_string($type)
            || $type !== 'urn:tms:acceptance-control:error:'.$code
            || ! $this->validProblemStatus($code, $status)) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }

        $exception = TargetControlException::because($code, $status);
        if ($exception->retryable !== $retryable) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }

        return $exception;
    }

    /** @return array<string, mixed> */
    private function decodeJson(Response $response, string $contentType): array
    {
        $body = $response->body();
        $actualContentType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
        if ($actualContentType !== $contentType || strlen($body) > $this->maxResponseBytes) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }

        try {
            $decoded = json_decode($body, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }
        if (! is_array($decoded) || array_is_list($decoded)) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }

        return $decoded;
    }

    /** @param array<string, mixed> $payload */
    private function mapCapabilities(array $payload): TargetControlCapabilities
    {
        $this->assertExactKeys($payload, [
            'protocol', 'server_version', 'max_lease_ttl_seconds', 'components',
        ]);
        $components = $payload['components'] ?? null;
        if (! is_array($components) || ! array_is_list($components)) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }

        $recipes = [];
        $observations = [];
        foreach ($components as $component) {
            if (! is_array($component) || array_is_list($component)) {
                throw TargetControlException::because('target_control_protocol_invalid');
            }
            $this->assertExactKeys($component, ['key', 'recipes', 'observations']);
            $key = $component['key'] ?? null;
            if (! is_string($key) || isset($recipes[$key])) {
                throw TargetControlException::because('target_control_protocol_invalid');
            }
            $recipes[$key] = $component['recipes'] ?? null;
            $observations[$key] = $component['observations'] ?? null;
        }

        try {
            return new TargetControlCapabilities(
                $payload['protocol'] ?? '',
                $payload['server_version'] ?? '',
                $payload['max_lease_ttl_seconds'] ?? 0,
                $recipes,
                $observations,
            );
        } catch (Throwable) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }
    }

    /** @param array<string, mixed> $payload */
    private function mapLeaseResult(
        array $payload,
        TargetControlLeaseRequest $request,
    ): TargetControlLeaseResult {
        $this->assertExactKeys($payload, [
            'protocol', 'lifecycle_id', 'request_digest', 'state',
            'references', 'lease_context', 'observation',
        ]);
        if (($payload['lifecycle_id'] ?? null) !== $request->lifecycleId
            || ($payload['request_digest'] ?? null) !== $request->requestDigest) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }

        $references = $this->mapReferences($payload['references'] ?? null);
        $leaseContext = $payload['lease_context'] ?? null;
        if ($leaseContext !== null) {
            $leaseContext = $this->mapReference($leaseContext);
        }

        $observation = $payload['observation'] ?? null;
        $observationKey = null;
        $facts = [];
        if ($observation !== null) {
            if (! is_array($observation) || array_is_list($observation)) {
                throw TargetControlException::because('target_control_protocol_invalid');
            }
            $this->assertExactKeys($observation, ['key', 'facts']);
            $observationKey = $observation['key'] ?? null;
            $facts = $observation['facts'] ?? null;
            if (! is_string($observationKey) || ! is_array($facts)) {
                throw TargetControlException::because('target_control_protocol_invalid');
            }
        }

        try {
            return new TargetControlLeaseResult(
                $payload['protocol'] ?? '',
                $payload['lifecycle_id'] ?? '',
                $payload['request_digest'] ?? '',
                $payload['state'] ?? '',
                $references,
                $leaseContext,
                $observationKey,
                $facts,
            );
        } catch (Throwable) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }
    }

    /** @return list<ResourceReference> */
    private function mapReferences(mixed $payload): array
    {
        if (! is_array($payload) || ! array_is_list($payload)) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }

        return array_map(fn (mixed $reference): ResourceReference => $this->mapReference($reference), $payload);
    }

    private function mapReference(mixed $payload): ResourceReference
    {
        if (! is_array($payload) || array_is_list($payload)) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }
        $this->assertExactKeys($payload, ['type', 'id', 'version']);

        try {
            return new ResourceReference(
                $payload['type'] ?? '',
                $payload['id'] ?? '',
                $payload['version'] ?? 0,
            );
        } catch (Throwable) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }
    }

    /** @param array<string, mixed> $payload @param list<string> $expected */
    private function assertExactKeys(array $payload, array $expected): void
    {
        $actual = array_keys($payload);
        sort($actual, SORT_STRING);
        sort($expected, SORT_STRING);
        if ($actual !== $expected) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }
    }

    private function validatedOrigin(string $endpoint): string
    {
        $parts = parse_url($endpoint);
        if (! is_array($parts)
            || ! isset($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])
            || ! in_array($parts['path'] ?? '', ['', '/'], true)) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }

        $scheme = $parts['scheme'] ?? '';
        $host = strtolower($parts['host']);
        $isHttps = $scheme === 'https';
        $isAllowedLoopbackHttp = $scheme === 'http'
            && $this->allowLocalLoopbackHttp
            && in_array($host, ['127.0.0.1', 'localhost', '[::1]'], true);

        if (! $isHttps && ! $isAllowedLoopbackHttp) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.$host.$port;
    }

    private function assertSecretHandle(string $secretHandle, string $appKey): void
    {
        if (preg_match(
            '/^app-secret:\/\/'.preg_quote($appKey, '/').'\/[A-Za-z0-9._~-]{1,128}$/D',
            $secretHandle,
        ) !== 1) {
            throw TargetControlException::because('target_control_protocol_invalid');
        }
    }

    private function validProblemStatus(string $code, int $status): bool
    {
        return match ($code) {
            'target_control_unavailable' => in_array($status, [502, 503, 504], true),
            'target_control_unauthorized' => in_array($status, [401, 403], true),
            'target_control_capability_mismatch' => $status === 412,
            'target_control_lease_conflict' => $status === 409,
            'target_control_recipe_rejected' => $status === 422,
            'target_control_cleanup_failed' => $status === 500,
            'target_control_protocol_invalid' => in_array($status, [400, 415, 500], true),
            default => false,
        };
    }
}
