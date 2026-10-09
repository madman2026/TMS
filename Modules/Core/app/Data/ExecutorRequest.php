<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use JsonSerializable;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Exceptions\ExecutorException;

final readonly class ExecutorRequest implements JsonSerializable
{
    private function __construct(
        public AcceptanceExecutionIdentity $identity,
        public ExecutorCapability $capability,
        public ExecutorTraceContext $trace,
        public ?AcceptanceScenario $scenario,
        public ?RunOptions $runOptions,
        public ?HttpExecutorRequest $http,
        public int $version,
    ) {
        $browserPayload = $scenario !== null && $runOptions !== null && $http === null;
        $httpPayload = $scenario === null && $runOptions === null && $http !== null;

        if ($version !== 1
            || (! $browserPayload && ! $httpPayload)
            || ($browserPayload && $capability->key !== ExecutorCapability::BROWSER)
            || ($httpPayload && $capability->key !== ExecutorCapability::HTTP)) {
            throw new ExecutorException('executor_request_invalid');
        }
    }

    public static function browser(
        AcceptanceExecutionIdentity $identity,
        AcceptanceScenario $scenario,
        RunOptions $runOptions,
        ?ExecutorTraceContext $trace = null,
    ): self {
        return new self(
            $identity,
            ExecutorCapability::browser(),
            $trace ?? new ExecutorTraceContext,
            $scenario,
            $runOptions,
            null,
            1,
        );
    }

    public static function http(
        AcceptanceExecutionIdentity $identity,
        HttpExecutorRequest $http,
        ?ExecutorTraceContext $trace = null,
    ): self {
        return new self(
            $identity,
            ExecutorCapability::http(),
            $trace ?? new ExecutorTraceContext,
            null,
            null,
            $http,
            1,
        );
    }

    public function isBrowser(): bool
    {
        return $this->capability->key === ExecutorCapability::BROWSER
            && $this->scenario !== null
            && $this->runOptions !== null
            && $this->http === null;
    }

    public function isHttp(): bool
    {
        return $this->capability->key === ExecutorCapability::HTTP
            && $this->scenario === null
            && $this->runOptions === null
            && $this->http !== null;
    }

    /** The execution request is an in-memory boundary and may contain transient target data. */
    public function __serialize(): array
    {
        throw new ExecutorException('executor_request_invalid');
    }

    public function jsonSerialize(): mixed
    {
        throw new ExecutorException('executor_request_invalid');
    }
}
