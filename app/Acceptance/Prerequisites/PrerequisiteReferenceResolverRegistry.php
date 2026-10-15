<?php

namespace App\Acceptance\Prerequisites;

use App\Acceptance\Prerequisites\Contracts\PrerequisiteReferenceResolver;
use InvalidArgumentException;

/** Composition-root registry for App-owned prerequisite reference resolvers. */
final class PrerequisiteReferenceResolverRegistry
{
    /** @var array<string, PrerequisiteReferenceResolver> */
    private array $resolvers = [];

    public function register(string $appKey, PrerequisiteReferenceResolver $resolver): void
    {
        $this->assertKey($appKey);
        if (isset($this->resolvers[$appKey])) {
            throw new InvalidArgumentException('prerequisite_reference_resolver_duplicate');
        }

        $this->resolvers[$appKey] = $resolver;
    }

    public function for(string $appKey): ?PrerequisiteReferenceResolver
    {
        $this->assertKey($appKey);

        return $this->resolvers[$appKey] ?? null;
    }

    private function assertKey(string $key): void
    {
        if (strlen($key) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1) {
            throw new InvalidArgumentException('prerequisite_reference_resolver_invalid');
        }
    }
}
