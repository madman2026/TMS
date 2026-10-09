<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use JsonSerializable;
use Modules\Core\Contracts\HttpResponseNormalizer;
use Modules\Core\Exceptions\ExecutorException;

final readonly class HttpExecutorRequest implements JsonSerializable
{
    public const DEFAULT_CONNECT_TIMEOUT_MS = 3_000;

    public const DEFAULT_TIMEOUT_MS = 10_000;

    public const MAX_BODY_BYTES = 1_048_576;

    /** @var array<string, string> */
    public array $headers;

    /** @var array<string, bool|float|int|string|null> */
    public array $query;

    /**
     * Target values remain transient and must never be serialized or logged.
     *
     * @param  array<string, string>  $headers
     * @param  array<string, bool|float|int|string|null>  $query
     */
    public function __construct(
        public string $method,
        public string $url,
        array $headers,
        array $query,
        public ?string $body,
        public string $contentType,
        public HttpResponseNormalizer $normalizer,
        public int $connectTimeoutMs = self::DEFAULT_CONNECT_TIMEOUT_MS,
        public int $timeoutMs = self::DEFAULT_TIMEOUT_MS,
        public int $maxResponseBytes = self::MAX_BODY_BYTES,
        public int $version = 1,
    ) {
        if ($version !== 1
            || ! in_array($method, ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], true)
            || ! self::safeUrl($url)
            || $connectTimeoutMs < 1 || $connectTimeoutMs > 10_000
            || $timeoutMs < 1 || $timeoutMs > 60_000 || $connectTimeoutMs > $timeoutMs
            || $maxResponseBytes < 1 || $maxResponseBytes > self::MAX_BODY_BYTES
            || ($body !== null && strlen($body) > self::MAX_BODY_BYTES)
            || $contentType === '' || strlen($contentType) > 128
            || preg_match('/^[!#$%&\'*+.^_`|~0-9A-Za-z-]+\/[!#$%&\'*+.^_`|~0-9A-Za-z-]+(?:\s*;\s*[^\r\n]+)?$/D', $contentType) !== 1
            || count($headers) > 64 || count($query) > 64) {
            throw new ExecutorException('executor_request_invalid');
        }

        $headerCopy = [];
        foreach ($headers as $name => $value) {
            if (! is_string($name) || ! is_string($value)
                || strlen($name) > 128 || strlen($value) > 8192
                || preg_match('/^[!#$%&\'*+.^_`|~0-9A-Za-z-]+$/D', $name) !== 1
                || str_contains($value, "\r") || str_contains($value, "\n")) {
                throw new ExecutorException('executor_request_invalid');
            }

            $headerCopy[$name] = $value;
        }

        $queryCopy = [];
        foreach ($query as $key => $value) {
            if (! is_string($key) || $key === '' || strlen($key) > 128
                || preg_match('//u', $key) !== 1 || preg_match('/[\p{C}]/u', $key) === 1
                || ! self::safeQueryValue($value)) {
                throw new ExecutorException('executor_request_invalid');
            }

            $queryCopy[$key] = $value;
        }

        $this->headers = $headerCopy;
        $this->query = $queryCopy;
    }

    public function __serialize(): array
    {
        throw new ExecutorException('executor_request_invalid');
    }

    public function jsonSerialize(): mixed
    {
        throw new ExecutorException('executor_request_invalid');
    }

    private static function safeUrl(string $url): bool
    {
        if ($url === '' || strlen($url) > 2048 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($url);

        return is_array($parts)
            && in_array($parts['scheme'] ?? null, ['http', 'https'], true)
            && is_string($parts['host'] ?? null)
            && ($parts['host'] ?? '') !== ''
            && ! isset($parts['user'])
            && ! isset($parts['pass'])
            && ! isset($parts['fragment']);
    }

    private static function safeQueryValue(mixed $value): bool
    {
        if ($value === null || is_bool($value) || is_int($value)) {
            return true;
        }

        if (is_float($value)) {
            return is_finite($value);
        }

        return is_string($value)
            && strlen($value) <= 8192
            && ! str_contains($value, "\r")
            && ! str_contains($value, "\n");
    }
}
