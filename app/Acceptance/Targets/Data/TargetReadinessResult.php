<?php

namespace App\Acceptance\Targets\Data;

use InvalidArgumentException;

final readonly class TargetReadinessResult
{
    public function __construct(
        public TargetEnvironment $environment,
        public bool $ready,
        public ?string $errorCode,
        public ?bool $retryable,
        public ?bool $permanent,
        public bool $adminActionRequired,
        public int $version = 1,
    ) {
        $validReady = $environment->safe() && $ready && $errorCode === null
            && $retryable === null && $permanent === null && ! $adminActionRequired;
        $validNotReady = $environment->safe() && ! $ready && $errorCode === 'target_not_ready'
            && $retryable === true && $permanent === false && ! $adminActionRequired;
        $validUnsafe = ! $environment->safe() && ! $ready && $errorCode === 'unsafe_target'
            && $retryable === false && $permanent === true && $adminActionRequired;
        if ($version !== 1 || (! $validReady && ! $validNotReady && ! $validUnsafe)) {
            throw new InvalidArgumentException('target_readiness_result_invalid');
        }
    }

    public static function ready(TargetEnvironment $environment): self
    {
        return new self($environment, true, null, null, null, false);
    }

    public static function notReady(TargetEnvironment $environment): self
    {
        return new self($environment, false, 'target_not_ready', true, false, false);
    }

    public static function unsafe(TargetEnvironment $environment): self
    {
        return new self($environment, false, 'unsafe_target', false, true, true);
    }
}
