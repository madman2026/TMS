<?php

namespace App\Acceptance\Targets\Data;

use InvalidArgumentException;

/** Safe semantic lifecycle result with primary and cleanup outcomes kept separate. */
final readonly class TargetResourceLifecycleData
{
    /** @var list<ResourceReference> */
    public array $references;

    /** @param list<ResourceReference> $references */
    public function __construct(
        public string $lifecycleId,
        public string $correlationId,
        public ?string $prerequisiteRequestId,
        public string $appKey,
        public string $componentKey,
        public string $suiteKey,
        public string $scenarioKey,
        public string $variantKey,
        public int $profileId,
        public string $status,
        public string $stage,
        public ?TargetReadinessResult $readiness,
        array $references,
        public ?TargetExecutionOutcome $execution,
        public ?TargetOracleResult $oracle,
        public ?CleanupResult $cleanup,
        public ?string $primaryErrorCode,
        public ?string $cleanupErrorCode,
        public int $version = 1,
    ) {
        if ($version !== 1 || ! self::isUuid($lifecycleId) || ! self::isUuid($correlationId)
            || ($prerequisiteRequestId !== null && ! self::isUuid($prerequisiteRequestId)) || $profileId < 1
            || ! in_array($status, ['succeeded', 'failed', 'cancelled'], true)
            || ! in_array($stage, ['registry', 'readiness', 'account', 'fixture', 'execution', 'oracle', 'cleanup', 'cancelled', 'complete'], true)
            || ! array_is_list($references)) {
            throw new InvalidArgumentException('target_resource_lifecycle_data_invalid');
        }
        foreach ([$appKey, $componentKey, $suiteKey, $scenarioKey, $variantKey] as $key) {
            if (strlen($key) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1) {
                throw new InvalidArgumentException('target_resource_lifecycle_data_invalid');
            }
        }
        foreach ([$primaryErrorCode, $cleanupErrorCode] as $code) {
            if ($code !== null && (strlen($code) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $code) !== 1)) {
                throw new InvalidArgumentException('target_resource_lifecycle_data_invalid');
            }
        }
        if ($cleanupErrorCode !== null && $cleanupErrorCode !== 'cleanup_failed') {
            throw new InvalidArgumentException('target_resource_lifecycle_data_invalid');
        }
        if (($cleanup?->succeeded === false) !== ($cleanupErrorCode === 'cleanup_failed')) {
            throw new InvalidArgumentException('target_resource_lifecycle_data_invalid');
        }
        $seen = [];
        foreach ($references as $reference) {
            if (! $reference instanceof ResourceReference || isset($seen[$reference->key()])) {
                throw new InvalidArgumentException('target_resource_lifecycle_data_invalid');
            }
            $seen[$reference->key()] = true;
        }
        $validSucceeded = $status === 'succeeded' && $stage === 'complete'
            && $primaryErrorCode === null && $cleanupErrorCode === null
            && $readiness?->ready === true && $execution?->status === 'succeeded'
            && $oracle?->passed === true && $cleanup?->succeeded === true;
        $validFailed = $status === 'failed' && ($primaryErrorCode !== null || $cleanupErrorCode !== null);
        $validCancelled = $status === 'cancelled' && $stage === 'cancelled' && $primaryErrorCode === null;
        if (! $validSucceeded && ! $validFailed && ! $validCancelled) {
            throw new InvalidArgumentException('target_resource_lifecycle_data_invalid');
        }
        $stageCodeValid = match ($stage) {
            'registry' => $primaryErrorCode === 'resource_unavailable' && $readiness === null,
            'readiness' => $primaryErrorCode === $readiness?->errorCode && $readiness?->ready === false,
            'account' => $primaryErrorCode === 'resource_unavailable' && $readiness?->ready === true,
            'fixture' => $primaryErrorCode === 'fixture_setup_failed' && $readiness?->ready === true,
            'execution' => $primaryErrorCode === $execution?->errorCode && $execution?->status === 'failed',
            'oracle' => $primaryErrorCode === 'oracle_failed' && $oracle?->passed === false,
            'cleanup' => $primaryErrorCode === null && $cleanupErrorCode === 'cleanup_failed',
            'cancelled' => $primaryErrorCode === null,
            'complete' => $primaryErrorCode === null && $cleanupErrorCode === null,
        };
        if (! $stageCodeValid) {
            throw new InvalidArgumentException('target_resource_lifecycle_data_invalid');
        }
        $this->references = array_values($references);
    }

    public static function fromContext(
        TargetContext $context,
        string $status,
        string $stage,
        ?TargetReadinessResult $readiness,
        ?TargetExecutionOutcome $execution,
        ?TargetOracleResult $oracle,
        ?CleanupResult $cleanup,
        ?string $primaryErrorCode,
        ?string $cleanupErrorCode,
    ): self {
        return new self(
            $context->lifecycleId,
            $context->correlationId,
            $context->prerequisiteRequestId,
            $context->appKey,
            $context->componentKey,
            $context->suiteKey,
            $context->scenarioKey,
            $context->variantKey,
            $context->profileId,
            $status,
            $stage,
            $readiness,
            $context->references,
            $execution,
            $oracle,
            $cleanup,
            $primaryErrorCode,
            $cleanupErrorCode,
        );
    }

    private static function isUuid(string $value): bool
    {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D', $value) === 1;
    }
}
