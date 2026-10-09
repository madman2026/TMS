<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use Modules\Core\Enums\ExecutorResultStatus;
use Modules\Core\Exceptions\ExecutorException;

final readonly class ExecutorResult
{
    public const ERROR_CODES = [
        'executor_request_invalid',
        'executor_not_found',
        'capability_unsupported',
        'browser_execution_failed',
        'http_transport_failed',
        'unsafe_executor_result',
    ];

    private function __construct(
        public AcceptanceExecutionIdentity $identity,
        public ExecutorCapability $capability,
        public ExecutorTraceContext $trace,
        public ?string $executorKey,
        public ExecutorResultStatus $status,
        public ?string $errorCode,
        public ?bool $retryable,
        public ?bool $permanent,
        public bool $adminActionRequired,
        public BrowserExecutorData|HttpExecutorData|null $data,
        public int $version = 1,
    ) {
        $validExecutor = $executorKey === null || self::safeKey($executorKey);
        $validData = ($data === null)
            || ($capability->key === ExecutorCapability::BROWSER && $data instanceof BrowserExecutorData)
            || ($capability->key === ExecutorCapability::HTTP && $data instanceof HttpExecutorData);
        $validSucceeded = $status === ExecutorResultStatus::Succeeded
            && $executorKey !== null && $errorCode === null && $retryable === null
            && $permanent === null && ! $adminActionRequired && $data !== null
            && (! $data instanceof BrowserExecutorData || $data->passed);
        $validFailed = $status === ExecutorResultStatus::Failed
            && $executorKey !== null && $errorCode !== null
            && ! in_array($errorCode, ['executor_not_found', 'capability_unsupported'], true)
            && $retryable !== null && $permanent !== null && $retryable !== $permanent
            && self::validFailureClassification(
                $capability,
                $errorCode,
                $retryable,
                $permanent,
                $adminActionRequired,
                $data,
            );
        $validUnsupported = $status === ExecutorResultStatus::Unsupported
            && in_array($errorCode, ['executor_not_found', 'capability_unsupported'], true)
            && $retryable === false && $permanent === true && $data === null
            && (($errorCode === 'executor_not_found' && $executorKey === null && $adminActionRequired)
                || ($errorCode === 'capability_unsupported' && $executorKey !== null && ! $adminActionRequired));

        if ($version !== 1 || ! $validExecutor || ! $validData
            || ($errorCode !== null && ! in_array($errorCode, self::ERROR_CODES, true))
            || (! $validSucceeded && ! $validFailed && ! $validUnsupported)) {
            throw new ExecutorException('unsafe_executor_result');
        }
    }

    public static function succeeded(
        ExecutorRequest $request,
        string $executorKey,
        BrowserExecutorData|HttpExecutorData $data,
    ): self {
        return new self(
            $request->identity,
            $request->capability,
            $request->trace,
            $executorKey,
            ExecutorResultStatus::Succeeded,
            null,
            null,
            null,
            false,
            $data,
        );
    }

    public static function failed(
        ExecutorRequest $request,
        string $executorKey,
        string $errorCode,
        bool $retryable,
        bool $permanent,
        bool $adminActionRequired,
        BrowserExecutorData|HttpExecutorData|null $data = null,
    ): self {
        return new self(
            $request->identity,
            $request->capability,
            $request->trace,
            $executorKey,
            ExecutorResultStatus::Failed,
            $errorCode,
            $retryable,
            $permanent,
            $adminActionRequired,
            $data,
        );
    }

    public static function unsupported(
        ExecutorRequest $request,
        ?string $executorKey,
        string $errorCode,
    ): self {
        return new self(
            $request->identity,
            $request->capability,
            $request->trace,
            $executorKey,
            ExecutorResultStatus::Unsupported,
            $errorCode,
            false,
            true,
            $errorCode === 'executor_not_found',
            null,
        );
    }

    public static function unsafe(ExecutorRequest $request, string $executorKey): self
    {
        return self::failed(
            $request,
            $executorKey,
            'unsafe_executor_result',
            false,
            true,
            true,
        );
    }

    public function matches(ExecutorRequest $request, ?string $executorKey = null): bool
    {
        return $this->identity === $request->identity
            && $this->capability === $request->capability
            && $this->trace === $request->trace
            && ($executorKey === null || $this->executorKey === $executorKey);
    }

    private static function safeKey(string $key): bool
    {
        return strlen($key) <= 64
            && preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) === 1;
    }

    private static function validFailureClassification(
        ExecutorCapability $capability,
        string $errorCode,
        bool $retryable,
        bool $permanent,
        bool $adminActionRequired,
        BrowserExecutorData|HttpExecutorData|null $data,
    ): bool {
        return match ($errorCode) {
            'executor_request_invalid' => ! $retryable && $permanent && ! $adminActionRequired && $data === null,
            'browser_execution_failed' => $capability->key === ExecutorCapability::BROWSER
                && $permanent === ! $retryable
                && $adminActionRequired === $retryable
                && ($data === null || ($data instanceof BrowserExecutorData && ! $data->passed)),
            'http_transport_failed' => $capability->key === ExecutorCapability::HTTP
                && $retryable && ! $permanent && ! $adminActionRequired && $data === null,
            'unsafe_executor_result' => ! $retryable && $permanent && $adminActionRequired && $data === null,
            default => false,
        };
    }
}
