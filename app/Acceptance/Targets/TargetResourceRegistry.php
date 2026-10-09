<?php

namespace App\Acceptance\Targets;

use InvalidArgumentException;

final class TargetResourceRegistry
{
    /** @var array<string, TargetResourceAdapters> */
    private array $adapters = [];

    public function register(string $appKey, TargetResourceAdapters $adapters): void
    {
        $this->assertKey($appKey);
        if (isset($this->adapters[$appKey])) {
            throw new InvalidArgumentException('target_resource_registry_duplicate');
        }
        $this->adapters[$appKey] = $adapters;
    }

    public function for(string $appKey): ?TargetResourceAdapters
    {
        $this->assertKey($appKey);

        return $this->adapters[$appKey] ?? null;
    }

    private function assertKey(string $key): void
    {
        if (strlen($key) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1) {
            throw new InvalidArgumentException('target_resource_registry_invalid');
        }
    }
}
