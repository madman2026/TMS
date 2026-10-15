<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Shared\Target;

use App\Acceptance\Targets\Control\Contracts\TargetControlClient;
use App\Acceptance\Targets\Control\Data\TargetControlLeaseRequest;
use App\Acceptance\Targets\Control\Data\TargetControlLeaseResult;
use App\Acceptance\Targets\Data\TargetContext;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\OperationsHealthVariant;
use Modules\DK\Acceptance\Shared\Target\Contracts\DKSyntheticTargetControl;

final readonly class DKTargetControlAdapter implements DKSyntheticTargetControl
{
    private const LEASE_TTL_SECONDS = 300;

    public function __construct(
        private TargetControlClient $client,
        private string $endpoint,
    ) {}

    public function acquire(
        TargetContext $context,
        OperationsHealthVariant $variant,
        string $secretHandle,
    ): TargetControlLeaseResult {
        return $this->client->acquireLease(
            $this->endpoint,
            $secretHandle,
            $this->request($context, $variant),
        );
    }

    public function inspect(
        TargetContext $context,
        OperationsHealthVariant $variant,
        string $secretHandle,
    ): TargetControlLeaseResult {
        return $this->client->inspectLease(
            $this->endpoint,
            $secretHandle,
            $this->request($context, $variant),
        );
    }

    public function cleanup(
        TargetContext $context,
        OperationsHealthVariant $variant,
        string $secretHandle,
        int $timeoutMs,
    ): TargetControlLeaseResult {
        if ($timeoutMs < 1) {
            throw new \InvalidArgumentException('acceptance_configuration_invalid');
        }

        return $this->client->cleanupLease(
            $this->endpoint,
            $secretHandle,
            $this->request($context, $variant),
        );
    }

    private function request(
        TargetContext $context,
        OperationsHealthVariant $variant,
    ): TargetControlLeaseRequest {
        return new TargetControlLeaseRequest(
            lifecycleId: $context->lifecycleId,
            correlationId: $context->correlationId,
            appKey: $context->appKey,
            componentKey: $context->componentKey,
            recipeKey: $variant->recipeKey,
            fixedInstant: OperationsHealthVariantCatalog::FIXED_INSTANT,
            leaseTtlSeconds: self::LEASE_TTL_SECONDS,
        );
    }
}
