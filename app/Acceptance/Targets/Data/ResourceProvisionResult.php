<?php

namespace App\Acceptance\Targets\Data;

use InvalidArgumentException;

final readonly class ResourceProvisionResult
{
    /** @var list<ResourceReference> */
    public array $references;

    /** @param list<ResourceReference> $references */
    public function __construct(
        public bool $succeeded,
        array $references,
        public ?string $errorCode,
        public ?bool $retryable,
        public ?bool $permanent,
        public bool $adminActionRequired,
        public int $version = 1,
    ) {
        if ($version !== 1 || ! array_is_list($references)) {
            throw new InvalidArgumentException('resource_provision_result_invalid');
        }
        $seen = [];
        foreach ($references as $reference) {
            if (! $reference instanceof ResourceReference || isset($seen[$reference->key()])) {
                throw new InvalidArgumentException('resource_provision_result_invalid');
            }
            $seen[$reference->key()] = true;
        }
        $validSuccess = $succeeded && $errorCode === null && $retryable === null
            && $permanent === null && ! $adminActionRequired;
        $validUnavailable = ! $succeeded && $errorCode === 'resource_unavailable'
            && $retryable === false && $permanent === true && $adminActionRequired;
        $validFixture = ! $succeeded && $errorCode === 'fixture_setup_failed'
            && $retryable === true && $permanent === false && $adminActionRequired;
        if (! $validSuccess && ! $validUnavailable && ! $validFixture) {
            throw new InvalidArgumentException('resource_provision_result_invalid');
        }
        $this->references = array_values($references);
    }

    /** @param list<ResourceReference> $references */
    public static function success(array $references): self
    {
        return new self(true, $references, null, null, null, false);
    }

    /** @param list<ResourceReference> $references */
    public static function unavailable(array $references = []): self
    {
        return new self(false, $references, 'resource_unavailable', false, true, true);
    }

    /** @param list<ResourceReference> $references */
    public static function fixtureFailed(array $references = []): self
    {
        return new self(false, $references, 'fixture_setup_failed', true, false, true);
    }
}
