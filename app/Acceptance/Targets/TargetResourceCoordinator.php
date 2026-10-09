<?php

namespace App\Acceptance\Targets;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Data\CleanupResult;
use App\Acceptance\Targets\Data\ResourceProvisionResult;
use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetContext;
use App\Acceptance\Targets\Data\TargetEnvironment;
use App\Acceptance\Targets\Data\TargetExecutionOutcome;
use App\Acceptance\Targets\Data\TargetOracleResult;
use App\Acceptance\Targets\Data\TargetReadinessResult;
use App\Acceptance\Targets\Data\TargetResourceLifecycleData;
use Closure;
use InvalidArgumentException;
use Throwable;

/** Coordinates the target-owned resource lifecycle without exposing target internals. */
final readonly class TargetResourceCoordinator
{
    public const CLEANUP_TIMEOUT_MS = 30_000;

    public function __construct(private TargetResourceRegistry $registry) {}

    /**
     * @param  Closure(TargetContext): TargetExecutionOutcome  $execute
     * @param  null|Closure(): bool  $isCancelled
     */
    public function execute(
        TargetContext $context,
        PrerequisiteExecutionData $prerequisites,
        Closure $execute,
        ?Closure $isCancelled = null,
    ): TargetResourceLifecycleData {
        $this->assertMatchingIdentity($context, $prerequisites);
        if ($this->cancelled($isCancelled)) {
            return $this->result($context, 'cancelled', 'cancelled');
        }

        $adapters = $this->registry->for($context->appKey);
        if ($adapters === null) {
            return $this->result($context, 'failed', 'registry', primaryErrorCode: 'resource_unavailable');
        }

        try {
            $readiness = $adapters->readiness->probe($context);
        } catch (Throwable) {
            $readiness = TargetReadinessResult::unsafe(TargetEnvironment::UNKNOWN);
        }
        if (! $readiness->ready) {
            return $this->result(
                $context,
                'failed',
                'readiness',
                readiness: $readiness,
                primaryErrorCode: $readiness->errorCode,
            );
        }
        if ($this->cancelled($isCancelled)) {
            return $this->result($context, 'cancelled', 'cancelled', readiness: $readiness);
        }

        $execution = null;
        $oracle = null;
        $cleanup = null;
        $primaryErrorCode = null;
        $primaryStage = 'complete';
        $cancelled = false;

        try {
            try {
                $account = $adapters->accounts->resolve($context, $prerequisites);
            } catch (Throwable) {
                $account = ResourceProvisionResult::unavailable();
            }
            $context = $context->withReferences($account->references);
            if (! $account->succeeded || $account->references === []) {
                $primaryErrorCode = $account->errorCode ?? 'resource_unavailable';
                $primaryStage = 'account';
            }

            if ($primaryErrorCode === null && $this->cancelled($isCancelled)) {
                $execution = TargetExecutionOutcome::cancelled();
                $primaryStage = 'cancelled';
                $cancelled = true;
            }

            if ($primaryErrorCode === null && $execution === null) {
                try {
                    $fixtures = $adapters->fixtures->provision($context, $prerequisites);
                } catch (Throwable) {
                    $fixtures = ResourceProvisionResult::fixtureFailed();
                }
                $context = $context->withReferences($fixtures->references);
                if (! $fixtures->succeeded) {
                    $primaryErrorCode = $fixtures->errorCode;
                    $primaryStage = 'fixture';
                }
            }

            if ($primaryErrorCode === null && $execution === null && $this->cancelled($isCancelled)) {
                $execution = TargetExecutionOutcome::cancelled();
                $primaryStage = 'cancelled';
                $cancelled = true;
            }

            if ($primaryErrorCode === null && $execution === null) {
                try {
                    $execution = $execute($context);
                    if (! $execution instanceof TargetExecutionOutcome) {
                        throw new InvalidArgumentException('target_execution_outcome_invalid');
                    }
                } catch (Throwable) {
                    $execution = TargetExecutionOutcome::failed(
                        'acceptance_command_failed', false, false, true, true,
                    );
                }
                if ($execution->status === 'failed') {
                    $primaryErrorCode = $execution->errorCode;
                    $primaryStage = 'execution';
                } elseif ($execution->status === 'cancelled') {
                    $primaryStage = 'cancelled';
                    $cancelled = true;
                }
            }

            if ($primaryErrorCode === null && ! $cancelled && $this->cancelled($isCancelled)) {
                $primaryStage = 'cancelled';
                $cancelled = true;
            }

            if (! $cancelled && $execution?->completed === true) {
                try {
                    $oracle = $adapters->oracle->evaluate($context, $execution);
                } catch (Throwable) {
                    $oracle = TargetOracleResult::failed();
                }
                if (! $oracle->passed && $primaryErrorCode === null) {
                    $primaryErrorCode = $oracle->errorCode;
                    $primaryStage = 'oracle';
                }
            }
        } finally {
            if ($context->references !== []) {
                try {
                    $cleanup = $this->validatedCleanup(
                        $context->references,
                        $adapters->cleanup->cleanup($context, self::CLEANUP_TIMEOUT_MS),
                    );
                } catch (Throwable) {
                    $cleanup = CleanupResult::failed([], $context->references);
                }
            }
        }

        $cleanupErrorCode = $cleanup?->succeeded === false ? 'cleanup_failed' : null;
        if ($cancelled) {
            return $this->result(
                $context, 'cancelled', 'cancelled', $readiness, $execution, $oracle, $cleanup, null, $cleanupErrorCode,
            );
        }
        if ($primaryErrorCode !== null) {
            return $this->result(
                $context, 'failed', $primaryStage, $readiness, $execution, $oracle, $cleanup,
                $primaryErrorCode, $cleanupErrorCode,
            );
        }
        if ($cleanupErrorCode !== null) {
            return $this->result(
                $context, 'failed', 'cleanup', $readiness, $execution, $oracle, $cleanup, null, $cleanupErrorCode,
            );
        }

        return $this->result($context, 'succeeded', 'complete', $readiness, $execution, $oracle, $cleanup);
    }

    /** @param list<ResourceReference> $expected */
    private function validatedCleanup(array $expected, CleanupResult $result): CleanupResult
    {
        $expectedKeys = array_map(fn (ResourceReference $reference): string => $reference->key(), $expected);
        $reportedKeys = array_map(
            fn (ResourceReference $reference): string => $reference->key(),
            [...$result->cleanedReferences, ...$result->remainingReferences],
        );
        sort($expectedKeys, SORT_STRING);
        sort($reportedKeys, SORT_STRING);

        return $expectedKeys === $reportedKeys ? $result : CleanupResult::failed([], $expected);
    }

    private function cancelled(?Closure $isCancelled): bool
    {
        if ($isCancelled === null) {
            return false;
        }
        try {
            return $isCancelled() === true;
        } catch (Throwable) {
            return true;
        }
    }

    private function assertMatchingIdentity(TargetContext $context, PrerequisiteExecutionData $prerequisites): void
    {
        if ($context->prerequisiteRequestId !== $prerequisites->requestId
            || $context->appKey !== $prerequisites->appKey
            || $context->componentKey !== $prerequisites->componentKey
            || $context->suiteKey !== $prerequisites->suiteKey
            || $context->scenarioKey !== $prerequisites->scenarioKey
            || $context->variantKey !== $prerequisites->variantKey
            || $context->profileId !== $prerequisites->profileId) {
            throw new InvalidArgumentException('target_resource_identity_mismatch');
        }
    }

    private function result(
        TargetContext $context,
        string $status,
        string $stage,
        ?TargetReadinessResult $readiness = null,
        ?TargetExecutionOutcome $execution = null,
        ?TargetOracleResult $oracle = null,
        ?CleanupResult $cleanup = null,
        ?string $primaryErrorCode = null,
        ?string $cleanupErrorCode = null,
    ): TargetResourceLifecycleData {
        return TargetResourceLifecycleData::fromContext(
            $context, $status, $stage, $readiness, $execution, $oracle, $cleanup,
            $primaryErrorCode, $cleanupErrorCode,
        );
    }
}
