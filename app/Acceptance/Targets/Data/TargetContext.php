<?php

namespace App\Acceptance\Targets\Data;

use InvalidArgumentException;

final readonly class TargetContext
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
        array $references = [],
        public int $version = 1,
    ) {
        if ($version !== 1 || ! self::isUuid($lifecycleId) || ! self::isUuid($correlationId)
            || ($prerequisiteRequestId !== null && ! self::isUuid($prerequisiteRequestId)) || $profileId < 1
            || ! array_is_list($references)) {
            throw new InvalidArgumentException('target_context_invalid');
        }
        foreach ([$appKey, $componentKey, $suiteKey, $scenarioKey, $variantKey] as $key) {
            if (strlen($key) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1) {
                throw new InvalidArgumentException('target_context_invalid');
            }
        }
        $seen = [];
        foreach ($references as $reference) {
            if (! $reference instanceof ResourceReference || isset($seen[$reference->key()])) {
                throw new InvalidArgumentException('target_context_invalid');
            }
            $seen[$reference->key()] = true;
        }
        $this->references = array_values($references);
    }

    /** @param list<ResourceReference> $references */
    public function withReferences(array $references): self
    {
        $merged = [];
        foreach ([...$this->references, ...$references] as $reference) {
            if (! $reference instanceof ResourceReference) {
                throw new InvalidArgumentException('target_context_invalid');
            }
            $merged[$reference->key()] = $reference;
        }

        return new self(
            $this->lifecycleId,
            $this->correlationId,
            $this->prerequisiteRequestId,
            $this->appKey,
            $this->componentKey,
            $this->suiteKey,
            $this->scenarioKey,
            $this->variantKey,
            $this->profileId,
            array_values($merged),
            $this->version,
        );
    }

    private static function isUuid(string $value): bool
    {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D', $value) === 1;
    }
}
