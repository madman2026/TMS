<?php

namespace App\Services;

use App\Contracts\AcceptanceCatalogProvider;
use App\Exceptions\AcceptanceCatalogException;
use App\Exceptions\AcceptanceRegistryException;
use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Data\ScenarioMetadata;
use Throwable;

final class AcceptanceAppRegistry
{
    /** @var array<string, AcceptanceApp> */
    private array $entries = [];

    /** @var array<string, array<string, array{scenario: AcceptanceScenario, metadata: ScenarioMetadata}>> */
    private array $legacySnapshots = [];

    /** Registration is identity-only; discovery belongs to explicit lookup/inspection. */
    public function register(AcceptanceApp $app): void
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

    public function app(string $key): ?AcceptanceApp
    {
        return $this->entries[$key] ?? null;
    }

    public function catalogProvider(string $appKey): ?AcceptanceCatalogProvider
    {
        $app = $this->app($appKey);

        return $app instanceof AcceptanceCatalogProvider ? $app : null;
    }

    public function scenario(string $appKey, string $scenarioKey): ?AcceptanceScenario
    {
        if ($this->app($appKey) === null) {
            return null;
        }
        if ($this->catalogProvider($appKey) !== null) {
            try {
                $dispatcher = new AcceptanceVariantDispatcher(new AcceptancePlanner(new AcceptanceCatalog($this)), $this);

                return $dispatcher->resolve($appKey, $scenarioKey);
            } catch (AcceptanceCatalogException $exception) {
                if ($exception->errorCode === 'acceptance_selector_not_found') {
                    return null;
                }

                throw $exception;
            }
        }

        return $this->legacySnapshot($appKey)[$scenarioKey]['scenario'] ?? null;
    }

    public function metadata(string $appKey, string $scenarioKey): ?ScenarioMetadata
    {
        if ($this->app($appKey) === null) {
            return null;
        }
        if ($this->catalogProvider($appKey) !== null) {
            return (new AcceptanceCatalog($this))->descriptor($appKey, $scenarioKey)?->metadata;
        }

        return $this->legacySnapshot($appKey)[$scenarioKey]['metadata'] ?? null;
    }

    /** @return array<int, string> */
    public function appKeys(): array
    {
        return array_keys($this->entries);
    }

    /** @return array<int, string> */
    public function scenarioKeys(string $appKey): array
    {
        if ($this->app($appKey) === null) {
            return [];
        }
        if ($this->catalogProvider($appKey) !== null) {
            $keys = [];
            foreach ((new AcceptanceCatalog($this))->descriptors($appKey) as $descriptor) {
                $keys[] = $descriptor->key;
            }

            return $keys;
        }

        return array_keys($this->legacySnapshot($appKey));
    }

    /** Validate a whole legacy App atomically; failure must leave no partial cache. */
    private function legacySnapshot(string $appKey): array
    {
        if (isset($this->legacySnapshots[$appKey])) {
            return $this->legacySnapshots[$appKey];
        }
        $scenarios = [];
        $visits = 0;
        try {
            foreach ($this->entries[$appKey]->scenarios() as $scenario) {
                AcceptanceCatalog::visit($visits);
                if (! $scenario instanceof AcceptanceScenario) {
                    throw AcceptanceRegistryException::invalid();
                }
                $key = $scenario->key();
                $this->assertValidKey($key);
                if (isset($scenarios[$key])) {
                    throw AcceptanceRegistryException::duplicate();
                }
                $metadata = $scenario->metadata();
                // Direct legacy execution keeps its existing key/classification contract.
                $scenarios[$key] = ['scenario' => $scenario, 'metadata' => $metadata];
            }
        } catch (AcceptanceRegistryException $exception) {
            throw $exception;
        } catch (AcceptanceCatalogException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw AcceptanceRegistryException::invalid();
        }

        return $this->legacySnapshots[$appKey] = $scenarios;
    }

    private function assertValidKey(string $key): void
    {
        if (! preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key)) {
            throw AcceptanceRegistryException::invalid();
        }
    }
}
