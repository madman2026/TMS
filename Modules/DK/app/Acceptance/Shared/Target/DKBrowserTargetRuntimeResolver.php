<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Shared\Target;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Contracts\TargetBrowserRuntimeResolver;
use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetContext;
use Modules\Core\Contracts\BrowserTargetRuntime;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Components\NotificationDelivery\Fixtures\OperationsHealthFixture;
use Modules\DK\Acceptance\Shared\Target\Contracts\DKSyntheticTargetControl;

final readonly class DKBrowserTargetRuntimeResolver implements TargetBrowserRuntimeResolver
{
    public function __construct(
        private DKSyntheticTargetControl $control,
        private OperationsHealthVariantCatalog $variants,
        private string $endpoint,
        private string $secretHandle,
    ) {}

    public function resolve(
        TargetContext $context,
        PrerequisiteExecutionData $prerequisites,
    ): BrowserTargetRuntime {
        $this->assertIdentity($context, $prerequisites);
        $variant = $this->variants->require($context->variantKey);
        $handle = $prerequisites->secretReferences[OperationsHealthFixture::SECRET_REFERENCE_KEY] ?? null;
        if ($handle !== $this->secretHandle) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        $leaseContexts = array_values(array_filter(
            $context->references,
            static fn (ResourceReference $reference): bool => $reference->type === 'acceptance-lease-context',
        ));
        if (count($leaseContexts) !== 1) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        return new DKBrowserTargetRuntime(
            $this->control,
            $context,
            $variant,
            $this->endpoint,
            $handle,
            $leaseContexts[0],
        );
    }

    private function assertIdentity(
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
            throw AcceptanceExecutionException::configurationInvalid();
        }
    }
}
