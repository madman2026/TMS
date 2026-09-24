<?php

namespace App\Services;

use App\Exceptions\AcceptanceRegistryException;
use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;

final class AcceptanceAppRegistry
{
    /**
     * @var array<string, array{app: AcceptanceApp, scenarios: array<string, AcceptanceScenario>}>
     */
    private array $entries = [];

    public function register(AcceptanceApp $app): void
    {
        $appKey = $app->key();
        $this->assertValidKey($appKey);

        if (isset($this->entries[$appKey])) {
            throw AcceptanceRegistryException::duplicate();
        }

        $scenarios = [];

        foreach ($app->scenarios() as $scenario) {
            if (! $scenario instanceof AcceptanceScenario) {
                throw AcceptanceRegistryException::invalid();
            }

            $scenarioKey = $scenario->key();
            $this->assertValidKey($scenarioKey);

            if (isset($scenarios[$scenarioKey])) {
                throw AcceptanceRegistryException::duplicate();
            }

            $scenarios[$scenarioKey] = $scenario;
        }

        $this->entries[$appKey] = [
            'app' => $app,
            'scenarios' => $scenarios,
        ];
    }

    public function app(string $key): ?AcceptanceApp
    {
        return $this->entries[$key]['app'] ?? null;
    }

    public function scenario(string $appKey, string $scenarioKey): ?AcceptanceScenario
    {
        return $this->entries[$appKey]['scenarios'][$scenarioKey] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function appKeys(): array
    {
        return array_keys($this->entries);
    }

    /**
     * @return array<int, string>
     */
    public function scenarioKeys(string $appKey): array
    {
        return array_keys($this->entries[$appKey]['scenarios'] ?? []);
    }

    private function assertValidKey(string $key): void
    {
        if (! preg_match('/^[a-z0-9][a-z0-9._-]*$/', $key)) {
            throw AcceptanceRegistryException::invalid();
        }
    }
}
