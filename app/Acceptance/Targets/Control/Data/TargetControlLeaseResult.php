<?php

namespace App\Acceptance\Targets\Control\Data;

use App\Acceptance\Targets\Control\TargetControlProtocol;
use App\Acceptance\Targets\Data\ResourceReference;
use InvalidArgumentException;

final readonly class TargetControlLeaseResult
{
    /** @var list<ResourceReference> */
    public array $references;

    /** @var array<string, bool|int|float|string|null> */
    public array $facts;

    /**
     * @param  list<ResourceReference>  $references
     * @param  array<string, bool|int|float|string|null>  $facts
     */
    public function __construct(
        public string $protocol,
        public string $lifecycleId,
        public string $requestDigest,
        public string $state,
        array $references,
        public ?ResourceReference $leaseContextReference = null,
        public ?string $observationKey = null,
        array $facts = [],
    ) {
        if ($protocol !== TargetControlProtocol::IDENTITY
            || ! TargetControlProtocol::validUuid($lifecycleId)
            || ! TargetControlProtocol::validDigest($requestDigest)
            || ! in_array($state, TargetControlProtocol::LEASE_STATES, true)
            || ! array_is_list($references)
            || count($references) > TargetControlProtocol::MAX_REFERENCES
            || (($observationKey === null) !== ($facts === []))
            || ($observationKey !== null && ! TargetControlProtocol::validToken($observationKey))
            || count($facts) > TargetControlProtocol::MAX_FACTS
            || ($facts !== [] && array_is_list($facts))) {
            throw new InvalidArgumentException('target_control_lease_result_invalid');
        }

        $seen = [];
        foreach ($references as $reference) {
            if (! $reference instanceof ResourceReference || isset($seen[$reference->key()])) {
                throw new InvalidArgumentException('target_control_lease_result_invalid');
            }
            $seen[$reference->key()] = true;
        }
        if ($leaseContextReference !== null && isset($seen[$leaseContextReference->key()])) {
            throw new InvalidArgumentException('target_control_lease_result_invalid');
        }

        $safeFacts = [];
        foreach ($facts as $key => $value) {
            if (! is_string($key) || ! TargetControlProtocol::validToken($key) || ! self::validFact($value)) {
                throw new InvalidArgumentException('target_control_lease_result_invalid');
            }
            $safeFacts[$key] = $value;
        }

        $this->references = array_values($references);
        $this->facts = $safeFacts;
    }

    private static function validFact(mixed $value): bool
    {
        if ($value === null || is_bool($value)) {
            return true;
        }
        if (is_int($value) || is_float($value)) {
            return is_finite((float) $value)
                && $value >= -1_000_000_000
                && $value <= 1_000_000_000;
        }

        return is_string($value) && TargetControlProtocol::validToken($value);
    }
}
