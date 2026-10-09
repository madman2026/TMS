<?php

namespace App\Acceptance\Execution\Data;

use InvalidArgumentException;

final readonly class BatchPrerequisiteReference
{
    public function __construct(
        public string $appKey,
        public string $componentKey,
        public string $suiteKey,
        public string $scenarioKey,
        public string $variantKey,
        public string $requestId,
        public int $version = 1,
    ) {
        if ($version !== 1 || preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D', $requestId) !== 1) {
            throw new InvalidArgumentException('batch_prerequisite_reference_invalid');
        }
        foreach ([$appKey, $componentKey, $suiteKey, $scenarioKey, $variantKey] as $key) {
            if (strlen($key) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1) {
                throw new InvalidArgumentException('batch_prerequisite_reference_invalid');
            }
        }
    }

    public function identity(): string
    {
        return implode("\0", [$this->appKey, $this->componentKey, $this->suiteKey, $this->scenarioKey, $this->variantKey]);
    }
}
