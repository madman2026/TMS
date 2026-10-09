<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Modules\Core\Contracts\AcceptanceExecutor;
use Modules\Core\Data\ExecutorCapability;
use Modules\Core\Data\ExecutorRequest;
use Modules\Core\Data\ExecutorResult;
use Modules\Core\Data\HttpExecutorData;
use Modules\Core\Exceptions\ExecutorException;
use Throwable;

final readonly class HttpAcceptanceExecutor implements AcceptanceExecutor
{
    public function __construct(private Factory $http) {}

    public function key(): string
    {
        return ExecutorCapability::HTTP;
    }

    public function capabilities(): array
    {
        return [ExecutorCapability::http()];
    }

    public function execute(ExecutorRequest $request): ExecutorResult
    {
        if (! $request->isHttp()) {
            return ExecutorResult::unsupported($request, $this->key(), 'capability_unsupported');
        }

        $httpRequest = $request->http;

        try {
            $pending = $this->http
                ->withHeaders($httpRequest->headers)
                ->connectTimeout($httpRequest->connectTimeoutMs / 1_000)
                ->timeout($httpRequest->timeoutMs / 1_000)
                ->withoutRedirecting()
                ->withOptions(['verify' => true]);

            if ($httpRequest->body !== null) {
                $pending = $pending->withBody($httpRequest->body, $httpRequest->contentType);
            }

            $response = $pending->send($httpRequest->method, $httpRequest->url, [
                'query' => $httpRequest->query,
            ]);
        } catch (ConnectionException $exception) {
            return $this->transportFailure($request);
        } catch (Throwable $exception) {
            return ExecutorResult::unsafe($request, $this->key());
        }

        try {
            $body = $response->body();
            if (strlen($body) > $httpRequest->maxResponseBytes) {
                throw new ExecutorException('unsafe_executor_result');
            }

            $normalized = $httpRequest->normalizer->normalize(
                $response->status(),
                $response->headers(),
                $body,
            );

            $data = new HttpExecutorData(
                $normalized->statusCode,
                $normalized->observations,
                $normalized->version,
            );

            if ($data->statusCode !== $response->status()) {
                throw new ExecutorException('unsafe_executor_result');
            }

            return ExecutorResult::succeeded($request, $this->key(), $data);
        } catch (Throwable $exception) {
            return ExecutorResult::unsafe($request, $this->key());
        }
    }

    private function transportFailure(ExecutorRequest $request): ExecutorResult
    {
        return ExecutorResult::failed(
            $request,
            $this->key(),
            'http_transport_failed',
            true,
            false,
            false,
        );
    }
}
