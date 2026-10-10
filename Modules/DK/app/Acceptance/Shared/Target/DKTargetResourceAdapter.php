<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Shared\Target;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Contracts\TargetAccountResolver;
use App\Acceptance\Targets\Contracts\TargetCleanup;
use App\Acceptance\Targets\Contracts\TargetFixtureManager;
use App\Acceptance\Targets\Contracts\TargetOracle;
use App\Acceptance\Targets\Contracts\TargetReadinessProbe;
use App\Acceptance\Targets\Data\CleanupResult;
use App\Acceptance\Targets\Data\ResourceProvisionResult;
use App\Acceptance\Targets\Data\TargetContext;
use App\Acceptance\Targets\Data\TargetExecutionOutcome;
use App\Acceptance\Targets\Data\TargetOracleResult;
use App\Acceptance\Targets\Data\TargetReadinessResult;

final readonly class DKTargetResourceAdapter implements TargetAccountResolver, TargetCleanup, TargetFixtureManager, TargetOracle, TargetReadinessProbe
{
    public function __construct(private DKTargetProfile $profile) {}

    public function probe(TargetContext $context): TargetReadinessResult
    {
        if (! $this->profile->environment->safe()) {
            return TargetReadinessResult::unsafe($this->profile->environment);
        }

        if (! $this->profile->enabled) {
            return TargetReadinessResult::notReady($this->profile->environment);
        }

        return TargetReadinessResult::ready($this->profile->environment);
    }

    public function resolve(
        TargetContext $context,
        PrerequisiteExecutionData $prerequisites,
    ): ResourceProvisionResult {
        return ResourceProvisionResult::unavailable();
    }

    public function provision(
        TargetContext $context,
        PrerequisiteExecutionData $prerequisites,
    ): ResourceProvisionResult {
        return ResourceProvisionResult::fixtureFailed();
    }

    public function evaluate(
        TargetContext $context,
        TargetExecutionOutcome $outcome,
    ): TargetOracleResult {
        return TargetOracleResult::failed();
    }

    public function cleanup(TargetContext $context, int $timeoutMs): CleanupResult
    {
        if ($context->references === []) {
            return CleanupResult::success([]);
        }

        return CleanupResult::failed([], $context->references);
    }
}
