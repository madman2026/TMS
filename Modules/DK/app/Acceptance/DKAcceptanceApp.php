<?php

namespace Modules\DK\Acceptance;

use App\Contracts\AcceptanceCoverageProvider;
use App\Data\ComponentDescriptor;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\DK\Acceptance\Coverage\SourceCaseMappings;

final class DKAcceptanceApp implements AcceptanceCoverageProvider
{
    public function key(): string
    {
        return 'dk';
    }

    public function catalogVersion(): string
    {
        return 'v2';
    }

    public function components(): iterable
    {
        foreach ($this->componentProviders() as $key => $component) {
            yield new ComponentDescriptor($key);
        }
    }

    public function suites(): iterable
    {
        foreach ($this->componentProviders() as $component) {
            yield from $component->suites();
        }
    }

    public function scenarios(): iterable
    {
        foreach ($this->componentProviders() as $component) {
            yield from $component->scenarios();
        }
    }

    public function variants(string $scenarioKey): iterable
    {
        foreach ($this->componentProviders() as $component) {
            yield from $component->variants($scenarioKey);
        }
    }

    public function sourceCaseMappings(): iterable
    {
        yield from SourceCaseMappings::all();
    }

    public function resolveScenario(
        string $componentKey,
        string $suiteKey,
        string $scenarioKey,
        string $variantKey,
    ): ?AcceptanceScenario {
        $components = $this->componentProviders();
        if (! isset($components[$componentKey])) {
            return null;
        }

        return $components[$componentKey]->resolveScenario(
            $suiteKey,
            $scenarioKey,
            $variantKey,
        );
    }

    /** @return array<string, object> */
    private function componentProviders(): array
    {
        return [
            // <tms:components>
            'notification-delivery' => new \Modules\DK\Acceptance\Components\NotificationDelivery\NotificationDeliveryAcceptanceComponent,
            // </tms:components>
        ];
    }
}
