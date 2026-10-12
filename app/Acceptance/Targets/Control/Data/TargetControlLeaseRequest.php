<?php

namespace App\Acceptance\Targets\Control\Data;

use App\Acceptance\Targets\Control\TargetControlProtocol;
use InvalidArgumentException;
use JsonException;

final readonly class TargetControlLeaseRequest
{
    public string $requestDigest;

    public function __construct(
        public string $lifecycleId,
        public string $correlationId,
        public string $appKey,
        public string $componentKey,
        public string $recipeKey,
        public string $fixedInstant,
        public int $leaseTtlSeconds,
    ) {
        if (! TargetControlProtocol::validUuid($lifecycleId)
            || ! TargetControlProtocol::validUuid($correlationId)
            || ! TargetControlProtocol::validToken($appKey)
            || ! TargetControlProtocol::validToken($componentKey)
            || ! TargetControlProtocol::validToken($recipeKey)
            || ! TargetControlProtocol::validFixedInstant($fixedInstant)
            || $leaseTtlSeconds < 1
            || $leaseTtlSeconds > TargetControlProtocol::MAX_LEASE_TTL_SECONDS) {
            throw new InvalidArgumentException('target_control_lease_request_invalid');
        }

        try {
            $canonical = json_encode(
                $this->canonicalPayload(),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
            );
        } catch (JsonException) {
            throw new InvalidArgumentException('target_control_lease_request_invalid');
        }

        $this->requestDigest = hash('sha256', $canonical);
    }

    /** @return array<string, int|string> */
    public function payload(): array
    {
        return [...$this->canonicalPayload(), 'request_digest' => $this->requestDigest];
    }

    /** @return array<string, int|string> */
    private function canonicalPayload(): array
    {
        return [
            'protocol' => TargetControlProtocol::IDENTITY,
            'lifecycle_id' => $this->lifecycleId,
            'correlation_id' => $this->correlationId,
            'app' => $this->appKey,
            'component' => $this->componentKey,
            'recipe' => $this->recipeKey,
            'fixed_instant' => $this->fixedInstant,
            'lease_ttl_seconds' => $this->leaseTtlSeconds,
        ];
    }
}
