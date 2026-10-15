<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Fixtures;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Data\CleanupResult;
use App\Acceptance\Targets\Data\ResourceProvisionResult;
use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetContext;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Shared\Target\Contracts\DKSyntheticTargetControl;
use Throwable;

final readonly class OperationsHealthFixture
{
    public const SECRET_REFERENCE_KEY = 'credential';

    public function __construct(
        private DKSyntheticTargetControl $control,
        private OperationsHealthVariantCatalog $variants,
        private string $secretHandle,
    ) {}

    public function provision(
        TargetContext $context,
        PrerequisiteExecutionData $prerequisites,
    ): ResourceProvisionResult {
        try {
            $this->assertMatchingIdentity($context, $prerequisites);
            $variant = $this->variants->require($context->variantKey);
            $handle = $prerequisites->secretReferences[self::SECRET_REFERENCE_KEY] ?? null;
            if ($handle !== $this->secretHandle) {
                return ResourceProvisionResult::fixtureFailed();
            }

            $result = $this->control->acquire($context, $variant, $handle);
            if ($result->state !== 'active' || $result->leaseContextReference === null) {
                return ResourceProvisionResult::fixtureFailed($result->references);
            }

            return ResourceProvisionResult::success($this->references($result->references, $result->leaseContextReference));
        } catch (Throwable) {
            return ResourceProvisionResult::fixtureFailed();
        }
    }

    public function cleanup(TargetContext $context, int $timeoutMs): CleanupResult
    {
        if ($context->references === []) {
            return CleanupResult::success([]);
        }

        try {
            $variant = $this->variants->require($context->variantKey);
            $result = $this->control->cleanup($context, $variant, $this->secretHandle, $timeoutMs);
            if ($result->state !== 'cleaned' || $result->references !== [] || $result->leaseContextReference !== null) {
                return CleanupResult::failed([], $context->references);
            }

            return CleanupResult::success($context->references);
        } catch (Throwable) {
            return CleanupResult::failed([], $context->references);
        }
    }

    private function assertMatchingIdentity(
        TargetContext $context,
        PrerequisiteExecutionData $prerequisites,
    ): void {
        if ($context->appKey !== 'dk'
            || $context->componentKey !== 'notification-delivery'
            || $context->suiteKey !== 'admin.delivery'
            || $context->scenarioKey !== 'nd-component-operations-health'
            || $context->appKey !== $prerequisites->appKey
            || $context->componentKey !== $prerequisites->componentKey
            || $context->suiteKey !== $prerequisites->suiteKey
            || $context->scenarioKey !== $prerequisites->scenarioKey
            || $context->variantKey !== $prerequisites->variantKey
            || $context->profileId !== $prerequisites->profileId
            || $context->prerequisiteRequestId !== $prerequisites->requestId) {
            throw new \InvalidArgumentException('acceptance_configuration_invalid');
        }
    }

    /**
     * @param  list<ResourceReference>  $references
     * @return list<ResourceReference>
     */
    private function references(array $references, ResourceReference $leaseContext): array
    {
        $all = [...$references, $leaseContext];
        $seen = [];
        foreach ($all as $reference) {
            if (isset($seen[$reference->key()])) {
                throw new \InvalidArgumentException('acceptance_configuration_invalid');
            }
            $seen[$reference->key()] = true;
        }

        return $all;
    }
}
