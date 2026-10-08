<?php

namespace Modules\Core\Data;

use InvalidArgumentException;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;

/**
 * Code-owned classification only; executable steps and sensitive target data
 * belong outside metadata.
 */
final readonly class ScenarioMetadata
{
    /** @var list<string> */
    public array $capabilities;

    /** @var list<string> */
    public array $tags;

    /**
     * @param  list<string>  $capabilities
     * @param  list<string>  $tags
     */
    public function __construct(
        array $capabilities,
        array $tags,
        public AutomationDisposition $disposition,
        public EvidenceMode $evidenceMode,
    ) {
        $this->capabilities = $this->validatedKeys($capabilities);
        $this->tags = $this->validatedKeys($tags);
    }

    /**
     * @param  list<string>  $keys
     * @return list<string>
     */
    private function validatedKeys(array $keys): array
    {
        $validated = [];
        $seen = [];

        foreach ($keys as $key) {
            if (! is_string($key)
                || ! preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key)
                || isset($seen[$key])) {
                throw new InvalidArgumentException('Scenario metadata must contain unique, valid keys.');
            }

            $seen[$key] = true;
            // Copy values so caller-owned references cannot bypass immutability.
            $validated[] = $key;
        }

        return $validated;
    }
}
