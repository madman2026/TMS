<?php

namespace App\Contracts;

use App\Data\ScenarioDescriptor;
use App\Data\VariantDescriptor;
use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;

/** Metadata discovery must not construct runnable scenarios or acquire accounts. */
interface AcceptanceCatalogProvider extends AcceptanceApp
{
    public function catalogVersion(): string;

    /** @return iterable<ScenarioDescriptor> */
    public function descriptors(): iterable;

    /** @return iterable<VariantDescriptor> */
    public function variants(string $scenarioKey): iterable;

    public function resolveScenario(string $scenarioKey, string $variantKey): ?AcceptanceScenario;
}
