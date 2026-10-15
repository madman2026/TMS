<?php

namespace App\Acceptance\Prerequisites\Data;

use InvalidArgumentException;

/** Bounded identity supplied to an Acceptance App reference resolver. */
final readonly class PrerequisiteReferenceContext
{
    public function __construct(
        public string $appKey,
        public string $componentKey,
        public string $suiteKey,
        public string $scenarioKey,
        public string $variantKey,
        public int $profileId,
        public string $schemaVersion,
        public string $inputKey,
    ) {
        foreach ([$appKey, $componentKey, $suiteKey, $scenarioKey, $variantKey, $schemaVersion, $inputKey] as $key) {
            InputRequirement::assertKey($key);
        }
        if ($profileId < 1) {
            throw new InvalidArgumentException('prerequisite_schema_invalid');
        }
    }
}
