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
use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetContext;
use App\Acceptance\Targets\Data\TargetExecutionOutcome;
use App\Acceptance\Targets\Data\TargetOracleResult;
use App\Acceptance\Targets\Data\TargetReadinessResult;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Components\NotificationDelivery\Fixtures\OperationsHealthFixture;

final readonly class DKTargetResourceAdapter implements TargetAccountResolver, TargetCleanup, TargetFixtureManager, TargetOracle, TargetReadinessProbe
{
    public function __construct(
        private DKTargetProfile $profile,
        private OperationsHealthFixture $fixture,
        private OperationsHealthVariantCatalog $variants,
    ) {}

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
        if (! $this->supports($context, $prerequisites)) {
            return ResourceProvisionResult::unavailable();
        }

        return ResourceProvisionResult::success([
            new ResourceReference(
                'account',
                hash('sha256', "dk\0super-user\0".$context->lifecycleId),
            ),
        ]);
    }

    public function provision(
        TargetContext $context,
        PrerequisiteExecutionData $prerequisites,
    ): ResourceProvisionResult {
        if (! $this->supports($context, $prerequisites)) {
            return ResourceProvisionResult::fixtureFailed();
        }

        return $this->fixture->provision($context, $prerequisites);
    }

    public function evaluate(
        TargetContext $context,
        TargetExecutionOutcome $outcome,
    ): TargetOracleResult {
        return $this->supportsContext($context) && $outcome->status === 'succeeded'
            ? TargetOracleResult::passed()
            : TargetOracleResult::failed();
    }

    public function cleanup(TargetContext $context, int $timeoutMs): CleanupResult
    {
        if ($context->references === []) {
            return CleanupResult::success([]);
        }

        if ($this->supportsContext($context)) {
            return $this->fixture->cleanup($context, $timeoutMs);
        }

        return CleanupResult::failed([], $context->references);
    }

    private function supports(
        TargetContext $context,
        PrerequisiteExecutionData $prerequisites,
    ): bool {
        return $this->supportsContext($context)
            && $context->appKey === $prerequisites->appKey
            && $context->componentKey === $prerequisites->componentKey
            && $context->suiteKey === $prerequisites->suiteKey
            && $context->scenarioKey === $prerequisites->scenarioKey
            && $context->variantKey === $prerequisites->variantKey
            && $context->profileId === $prerequisites->profileId
            && $context->prerequisiteRequestId === $prerequisites->requestId
            && ($prerequisites->secretReferences[OperationsHealthFixture::SECRET_REFERENCE_KEY] ?? null)
                === LocalLoopbackSecretHandleResolver::HANDLE;
    }

    private function supportsContext(TargetContext $context): bool
    {
        return $context->appKey === 'dk'
            && $context->componentKey === 'notification-delivery'
            && $context->suiteKey === 'admin.delivery'
            && $context->scenarioKey === 'nd-component-operations-health'
            && $this->variants->find($context->variantKey) !== null;
    }
}
