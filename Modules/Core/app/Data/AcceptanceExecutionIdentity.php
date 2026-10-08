<?php

namespace Modules\Core\Data;

use InvalidArgumentException;

/** Immutable executable identity shared by orchestration and target-neutral runners. */
final readonly class AcceptanceExecutionIdentity
{
    public function __construct(
        public string $appKey,
        public string $componentKey,
        public string $suiteKey,
        public string $scenarioKey,
        public string $variantKey,
    ) {
        foreach ([$appKey, $componentKey, $suiteKey, $scenarioKey, $variantKey] as $key) {
            if (strlen($key) > 64 || ! preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key)) {
                throw new InvalidArgumentException('Acceptance execution identity is invalid.');
            }
        }
    }

    public function value(): string
    {
        return implode("\0", [
            $this->appKey,
            $this->componentKey,
            $this->suiteKey,
            $this->scenarioKey,
            $this->variantKey,
        ]);
    }
}
