<?php

namespace App\Services;

use App\Data\ScenarioDescriptor;
use App\Data\VariantDescriptor;
use App\Exceptions\AcceptanceCatalogException;
use App\Exceptions\AcceptanceRegistryException;
use InvalidArgumentException;
use Throwable;
use TypeError;

/** Validates only visited metadata and never invokes runnable resolution. */
final class AcceptanceCatalog
{
    public const MAX_VISITS = 10000;

    public function __construct(private readonly AcceptanceAppRegistry $registry) {}

    public function appKeys(): array
    {
        $keys = $this->registry->appKeys();
        sort($keys, SORT_STRING);

        return $keys;
    }

    public function version(string $appKey): string
    {
        if ($this->registry->app($appKey) === null) {
            throw AcceptanceCatalogException::because('acceptance_app_not_found');
        }
        try {
            return ScenarioDescriptor::assertKey($this->registry->catalogProvider($appKey)?->catalogVersion() ?? 'legacy-v1');
        } catch (AcceptanceCatalogException $exception) {
            throw $exception;
        } catch (TypeError) {
            throw AcceptanceCatalogException::because('acceptance_catalog_invalid');
        } catch (Throwable) {
            throw AcceptanceCatalogException::because('acceptance_catalog_failed');
        }
    }

    /** @return iterable<ScenarioDescriptor> */
    public function descriptors(string $appKey): iterable
    {
        try {
            $provider = $this->registry->catalogProvider($appKey);
            $descriptors = $provider !== null ? $provider->descriptors() : $this->legacyDescriptors($appKey);
            $seen = [];
            $visits = 0;
            foreach ($descriptors as $descriptor) {
                self::visit($visits);
                if (! $descriptor instanceof ScenarioDescriptor) {
                    throw AcceptanceCatalogException::because('acceptance_catalog_invalid');
                }
                if (isset($seen[$descriptor->key])) {
                    throw AcceptanceCatalogException::because('acceptance_catalog_duplicate');
                }
                $seen[$descriptor->key] = true;
                yield $descriptor;
            }
        } catch (AcceptanceCatalogException $exception) {
            throw $exception;
        } catch (AcceptanceRegistryException $exception) {
            throw AcceptanceCatalogException::because($exception->errorCode === 'acceptance_registry_duplicate'
                ? 'acceptance_catalog_duplicate' : 'acceptance_catalog_invalid');
        } catch (InvalidArgumentException|TypeError) {
            throw AcceptanceCatalogException::because('acceptance_catalog_invalid');
        } catch (Throwable) {
            throw AcceptanceCatalogException::because('acceptance_catalog_failed');
        }
    }

    /** @return iterable<VariantDescriptor> */
    public function variants(string $appKey, string $scenarioKey): iterable
    {
        try {
            $provider = $this->registry->catalogProvider($appKey);
            $variants = $provider !== null ? $provider->variants($scenarioKey) : [new VariantDescriptor('default')];
            $seen = [];
            $visits = 0;
            foreach ($variants as $variant) {
                self::visit($visits);
                if (! $variant instanceof VariantDescriptor) {
                    throw AcceptanceCatalogException::because('acceptance_catalog_invalid');
                }
                if (isset($seen[$variant->key])) {
                    throw AcceptanceCatalogException::because('acceptance_catalog_duplicate');
                }
                $seen[$variant->key] = true;
                yield $variant;
            }
        } catch (AcceptanceCatalogException $exception) {
            throw $exception;
        } catch (InvalidArgumentException|TypeError) {
            throw AcceptanceCatalogException::because('acceptance_catalog_invalid');
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

    private function legacyDescriptors(string $appKey): iterable
    {
        foreach ($this->registry->scenarioKeys($appKey) as $key) {
            yield new ScenarioDescriptor($key, $this->registry->metadata($appKey, $key));
        }
    }
}
