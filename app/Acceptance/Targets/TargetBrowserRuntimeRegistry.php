<?php

namespace App\Acceptance\Targets;

use App\Acceptance\Targets\Contracts\TargetBrowserRuntimeResolver;
use InvalidArgumentException;

final class TargetBrowserRuntimeRegistry
{
    /** @var array<string, TargetBrowserRuntimeResolver> */
    private array $resolvers = [];

    public function register(string $appKey, TargetBrowserRuntimeResolver $resolver): void
    {
        $this->assertKey($appKey);
        if (isset($this->resolvers[$appKey])) {
            throw new InvalidArgumentException('target_resource_registry_duplicate');
        }

        $this->resolvers[$appKey] = $resolver;
    }

    public function for(string $appKey): ?TargetBrowserRuntimeResolver
    {
        $this->assertKey($appKey);

        return $this->resolvers[$appKey] ?? null;
    }

    private function assertKey(string $key): void
    {
        if (strlen($key) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1) {
            throw new InvalidArgumentException('target_resource_registry_invalid');
        }
    }
}
