<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Shared\Prerequisites;

use App\Acceptance\Prerequisites\Contracts\PrerequisiteReferenceResolver;
use App\Acceptance\Prerequisites\Data\PrerequisiteReferenceContext;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Shared\Target\LocalLoopbackSecretHandleResolver;

/** Resolves only the accepted DK Operations Health opaque reference. */
final readonly class DKPrerequisiteReferenceResolver implements PrerequisiteReferenceResolver
{
    public function __construct(private OperationsHealthVariantCatalog $variants) {}

    public function resolve(PrerequisiteReferenceContext $context): ?string
    {
        if ($context->appKey !== 'dk'
            || $context->componentKey !== 'notification-delivery'
            || $context->suiteKey !== 'admin.delivery'
            || $context->scenarioKey !== 'nd-component-operations-health'
            || $context->schemaVersion !== 'operations-health-v2'
            || $context->inputKey !== 'credential'
            || $this->variants->find($context->variantKey) === null) {
            return null;
        }

        return LocalLoopbackSecretHandleResolver::HANDLE;
    }
}
