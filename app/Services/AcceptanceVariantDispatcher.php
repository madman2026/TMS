<?php

namespace App\Services;

use App\Data\ScenarioDescriptor;
use App\Exceptions\AcceptanceCatalogException;
use InvalidArgumentException;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Throwable;
use TypeError;

/** Materializes exactly one validated hierarchy tuple. */
final class AcceptanceVariantDispatcher
{
    public function __construct(
        private readonly AcceptancePlanner $planner,
        private readonly AcceptanceAppRegistry $registry,
    ) {}

    public function resolve(AcceptanceExecutionIdentity $identity): AcceptanceScenario
    {
        $plan = $this->planner->planExact($identity);
        $item = $plan->items[0] ?? null;
        if ($item === null || $item->identity() !== $identity->value()) {
            throw AcceptanceCatalogException::because('acceptance_selector_not_found');
        }
        if (! $item->executable()) {
            throw AcceptanceCatalogException::because('acceptance_variant_not_executable');
        }

        try {
            $provider = $this->registry->app($identity->appKey)
                ?? throw AcceptanceCatalogException::because('acceptance_app_not_found');
            $scenario = $provider->resolveScenario(
                $identity->componentKey,
                $identity->suiteKey,
                $identity->scenarioKey,
                $identity->variantKey,
            );

            if (ScenarioDescriptor::assertKey($provider->catalogVersion()) !== $plan->catalogVersions[$identity->appKey]) {
                throw AcceptanceCatalogException::because('acceptance_catalog_changed');
            }
            if (! $scenario instanceof AcceptanceScenario || $scenario->key() !== $identity->scenarioKey
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
