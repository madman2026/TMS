<?php

namespace App\Services;

use App\Data\AcceptanceSelector;
use App\Data\ScenarioDescriptor;
use App\Exceptions\AcceptanceCatalogException;
use InvalidArgumentException;
use Modules\Core\Contracts\AcceptanceScenario;
use Throwable;
use TypeError;

/** Materializes one eligible tuple; execution remains owned by the existing runner. */
final class AcceptanceVariantDispatcher
{
    public function __construct(
        private readonly AcceptancePlanner $planner,
        private readonly AcceptanceAppRegistry $registry,
    ) {}

    public function resolve(string $appKey, string $scenarioKey, string $variantKey = 'default'): AcceptanceScenario
    {
        $plan = $this->planner->plan(new AcceptanceSelector(
            apps: [$appKey], scenarios: [$scenarioKey], variants: [$variantKey], limit: 1,
        ));
        $item = $plan->items[0] ?? null;
        if ($item === null) {
            throw AcceptanceCatalogException::because('acceptance_selector_not_found');
        }
        if (! $item->executable()) {
            throw AcceptanceCatalogException::because('acceptance_variant_not_executable');
        }

        try {
            $provider = $this->registry->catalogProvider($appKey);
            $scenario = $provider !== null
                ? $provider->resolveScenario($scenarioKey, $variantKey)
                : $this->registry->scenario($appKey, $scenarioKey);

            if ($provider !== null
                && ScenarioDescriptor::assertKey($provider->catalogVersion()) !== $plan->catalogVersions[$appKey]) {
                throw AcceptanceCatalogException::because('acceptance_catalog_changed');
            }
            if (! $scenario instanceof AcceptanceScenario || $scenario->key() !== $scenarioKey
                || ScenarioDescriptor::classification($scenario->metadata()) !== ScenarioDescriptor::classification($item->metadata)) {
                throw AcceptanceCatalogException::because('acceptance_catalog_invalid');
            }

            return $scenario;
        } catch (AcceptanceCatalogException $exception) {
            throw $exception;
        } catch (InvalidArgumentException|TypeError) {
            throw AcceptanceCatalogException::because('acceptance_catalog_invalid');
        } catch (Throwable) {
            throw AcceptanceCatalogException::because('acceptance_catalog_failed');
        }
    }
}
