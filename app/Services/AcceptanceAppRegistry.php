<?php

namespace App\Services;

use App\Contracts\AcceptanceComponentProvider;
use App\Exceptions\AcceptanceRegistryException;
use Throwable;

final class AcceptanceAppRegistry
{
    /** @var array<string, AcceptanceComponentProvider> */
    private array $entries = [];

    /** Registration validates identity only; hierarchy inspection remains lazy. */
    public function register(AcceptanceComponentProvider $app): void
    {
        try {
            $appKey = $app->key();
        } catch (Throwable) {
            throw AcceptanceRegistryException::invalid();
        }
        $this->assertValidKey($appKey);

        if (isset($this->entries[$appKey])) {
            throw AcceptanceRegistryException::duplicate();
        }

        $this->entries[$appKey] = $app;
    }

    public function app(string $key): ?AcceptanceComponentProvider
    {
        return $this->entries[$key] ?? null;
    }

    /** @return list<string> */
    public function appKeys(): array
    {
        return array_keys($this->entries);
    }

    private function assertValidKey(string $key): void
    {
        if (strlen($key) > 64 || ! preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key)) {
            throw AcceptanceRegistryException::invalid();
        }
    }
}
