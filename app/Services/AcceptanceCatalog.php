<?php

namespace App\Services;

use App\Contracts\AcceptanceComponentProvider;
use App\Data\ComponentDescriptor;
use App\Data\ScenarioDescriptor;
use App\Data\SuiteDescriptor;
use App\Data\VariantDescriptor;
use App\Exceptions\AcceptanceCatalogException;
use InvalidArgumentException;
use Throwable;
use TypeError;

/** Validates the complete hierarchy without materializing executable scenarios. */
final class AcceptanceCatalog
{
    public const MAX_VISITS = 10000;

    public function __construct(private readonly AcceptanceAppRegistry $registry) {}

    /** @return list<string> */
    public function appKeys(): array
    {
        $keys = $this->registry->appKeys();
        sort($keys, SORT_STRING);

        return $keys;
    }

    public function version(string $appKey): string
    {
        $provider = $this->provider($appKey);

        try {
            return ScenarioDescriptor::assertKey($provider->catalogVersion());
        } catch (AcceptanceCatalogException $exception) {
            throw $exception;
        } catch (TypeError) {
            throw AcceptanceCatalogException::because('acceptance_catalog_invalid');
        } catch (Throwable) {
            throw AcceptanceCatalogException::because('acceptance_catalog_failed');
        }
    }

    /** @return iterable<ComponentDescriptor> */
    public function components(string $appKey): iterable
    {
        try {
            $seen = [];
            $visits = 0;
            foreach ($this->provider($appKey)->components() as $component) {
                self::visit($visits);
                if (! $component instanceof ComponentDescriptor) {
                    throw AcceptanceCatalogException::because('acceptance_hierarchy_invalid');
                }
                if (isset($seen[$component->key])) {
                    throw AcceptanceCatalogException::because('acceptance_hierarchy_duplicate');
                }
                $seen[$component->key] = true;
                yield $component;
            }
        } catch (AcceptanceCatalogException $exception) {
            throw $exception;
        } catch (InvalidArgumentException|TypeError) {
            throw AcceptanceCatalogException::because('acceptance_hierarchy_invalid');
        } catch (Throwable) {
            throw AcceptanceCatalogException::because('acceptance_catalog_failed');
        }
    }

    /** @return iterable<SuiteDescriptor> */
    public function suites(string $appKey): iterable
    {
        try {
            $components = [];
            foreach ($this->components($appKey) as $component) {
                $components[$component->key] = true;
            }
            $seen = [];
            $visits = 0;
            foreach ($this->provider($appKey)->suites() as $suite) {
                self::visit($visits);
                if (! $suite instanceof SuiteDescriptor || ! isset($components[$suite->componentKey])) {
                    throw AcceptanceCatalogException::because('acceptance_hierarchy_invalid');
                }
                if (isset($seen[$suite->key])) {
                    throw AcceptanceCatalogException::because('acceptance_hierarchy_duplicate');
                }
                $seen[$suite->key] = true;
                yield $suite;
            }
        } catch (AcceptanceCatalogException $exception) {
            throw $exception;
        } catch (InvalidArgumentException|TypeError) {
            throw AcceptanceCatalogException::because('acceptance_hierarchy_invalid');
        } catch (Throwable) {
            throw AcceptanceCatalogException::because('acceptance_catalog_failed');
        }
    }

    /** @return iterable<ScenarioDescriptor> */
    public function descriptors(string $appKey): iterable
    {
        try {
            $components = [];
            foreach ($this->components($appKey) as $component) {
                $components[$component->key] = true;
            }
            $suites = [];
            foreach ($this->suites($appKey) as $suite) {
                $suites[$suite->key] = $suite->componentKey;
            }
            $seen = [];
            $visits = 0;
            foreach ($this->provider($appKey)->scenarios() as $descriptor) {
                self::visit($visits);
                if (! $descriptor instanceof ScenarioDescriptor
                    || ! isset($components[$descriptor->componentKey])
                    || ($suites[$descriptor->suiteKey] ?? null) !== $descriptor->componentKey) {
                    throw AcceptanceCatalogException::because('acceptance_hierarchy_invalid');
                }
                if (isset($seen[$descriptor->key])) {
                    throw AcceptanceCatalogException::because('acceptance_hierarchy_duplicate');
                }
                $seen[$descriptor->key] = true;
                yield $descriptor;
            }
        } catch (AcceptanceCatalogException $exception) {
            throw $exception;
        } catch (InvalidArgumentException|TypeError) {
            throw AcceptanceCatalogException::because('acceptance_hierarchy_invalid');
        } catch (Throwable) {
            throw AcceptanceCatalogException::because('acceptance_catalog_failed');
        }
    }

    /** @return iterable<VariantDescriptor> */
    public function variants(string $appKey, string $scenarioKey): iterable
    {
        try {
            $seen = [];
            $visits = 0;
            foreach ($this->provider($appKey)->variants($scenarioKey) as $variant) {
                self::visit($visits);
                if (! $variant instanceof VariantDescriptor) {
                    throw AcceptanceCatalogException::because('acceptance_hierarchy_invalid');
                }
                if (isset($seen[$variant->key])) {
                    throw AcceptanceCatalogException::because('acceptance_hierarchy_duplicate');
                }
                $seen[$variant->key] = true;
                yield $variant;
            }
        } catch (AcceptanceCatalogException $exception) {
            throw $exception;
        } catch (InvalidArgumentException|TypeError) {
            throw AcceptanceCatalogException::because('acceptance_hierarchy_invalid');
        } catch (Throwable) {
            throw AcceptanceCatalogException::because('acceptance_catalog_failed');
        }
    }

    public function descriptor(string $appKey, string $scenarioKey): ?ScenarioDescriptor
    {
        foreach ($this->descriptors($appKey) as $descriptor) {
            if ($descriptor->key === $scenarioKey) {
                return $descriptor;
            }
        }

        return null;
    }

    /** Observe one overflow element, then stop before advancing its generator. */
    public static function visit(int &$visits): void
    {
        if (++$visits > self::MAX_VISITS) {
            throw AcceptanceCatalogException::because('acceptance_catalog_limit_exceeded');
        }
    }

    private function provider(string $appKey): AcceptanceComponentProvider
    {
        return $this->registry->app($appKey)
            ?? throw AcceptanceCatalogException::because('acceptance_app_not_found');
    }
}
