<?php

namespace App\Acceptance\Targets\Data;

use InvalidArgumentException;

final readonly class CleanupResult
{
    /** @var list<ResourceReference> */
    public array $cleanedReferences;

    /** @var list<ResourceReference> */
    public array $remainingReferences;

    /**
     * @param  list<ResourceReference>  $cleanedReferences
     * @param  list<ResourceReference>  $remainingReferences
     */
    public function __construct(
        public bool $succeeded,
        array $cleanedReferences,
        array $remainingReferences,
        public bool $timedOut,
        public ?string $errorCode,
        public ?bool $retryable,
        public ?bool $permanent,
        public bool $adminActionRequired,
        public int $version = 1,
    ) {
        if ($version !== 1 || ! array_is_list($cleanedReferences) || ! array_is_list($remainingReferences)) {
            throw new InvalidArgumentException('cleanup_result_invalid');
        }
        $seen = [];
        foreach ([...$cleanedReferences, ...$remainingReferences] as $reference) {
            if (! $reference instanceof ResourceReference || isset($seen[$reference->key()])) {
                throw new InvalidArgumentException('cleanup_result_invalid');
            }
            $seen[$reference->key()] = true;
        }
        $validSuccess = $succeeded && $remainingReferences === [] && ! $timedOut && $errorCode === null
            && $retryable === null && $permanent === null && ! $adminActionRequired;
        $validFailure = ! $succeeded && $errorCode === 'cleanup_failed'
            && $retryable === true && $permanent === false && $adminActionRequired;
        if (! $validSuccess && ! $validFailure) {
            throw new InvalidArgumentException('cleanup_result_invalid');
        }
        $this->cleanedReferences = array_values($cleanedReferences);
        $this->remainingReferences = array_values($remainingReferences);
    }

    /** @param list<ResourceReference> $references */
    public static function success(array $references): self
    {
        return new self(true, $references, [], false, null, null, null, false);
    }

    /** @param list<ResourceReference> $cleaned @param list<ResourceReference> $remaining */
    public static function failed(array $cleaned = [], array $remaining = [], bool $timedOut = false): self
    {
        return new self(false, $cleaned, $remaining, $timedOut, 'cleanup_failed', true, false, true);
    }
}
