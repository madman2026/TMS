<?php

namespace App\Contracts;

use App\Data\ComponentDescriptor;
use App\Data\ScenarioDescriptor;
use App\Data\SuiteDescriptor;
use App\Data\VariantDescriptor;
use Modules\Core\Contracts\AcceptanceScenario;

/** Metadata inspection is side-effect free; runnable scenarios resolve by one exact hierarchy tuple. */
interface AcceptanceComponentProvider
{
    public function key(): string;

    public function catalogVersion(): string;

    /** @return iterable<ComponentDescriptor> */
    public function components(): iterable;

    /** @return iterable<SuiteDescriptor> */
    public function suites(): iterable;

    /** @return iterable<ScenarioDescriptor> */
    public function scenarios(): iterable;

    /** @return iterable<VariantDescriptor> */
    public function variants(string $scenarioKey): iterable;

    public function resolveScenario(
        string $componentKey,
        string $suiteKey,
        string $scenarioKey,
        string $variantKey,
    ): ?AcceptanceScenario;
}
