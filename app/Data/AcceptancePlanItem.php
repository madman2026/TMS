<?php

namespace App\Data;

use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;

/** An immutable safe tuple, never an executable scenario or a target payload. */
final readonly class AcceptancePlanItem
{
    public function __construct(
        public string $appKey,
        public string $scenarioKey,
        public string $variantKey,
        public ScenarioMetadata $metadata,
    ) {
        ScenarioDescriptor::assertKey($appKey);
        ScenarioDescriptor::assertKey($scenarioKey);
        ScenarioDescriptor::assertKey($variantKey);
        ScenarioDescriptor::classification($metadata);
    }

    public function executable(): bool
    {
        return $this->metadata->disposition === AutomationDisposition::AUTOMATED;
    }

    public function toArray(): array
    {
        return [
            'app_key' => $this->appKey,
            'scenario_key' => $this->scenarioKey,
            'variant_key' => $this->variantKey,
            ...ScenarioDescriptor::classification($this->metadata),
            'executable' => $this->executable(),
        ];
    }

    public function identity(): string
    {
        // NUL cannot occur in validated keys, keeping tuple comparison unambiguous.
        return implode("\0", [$this->appKey, $this->scenarioKey, $this->variantKey]);
    }
}
