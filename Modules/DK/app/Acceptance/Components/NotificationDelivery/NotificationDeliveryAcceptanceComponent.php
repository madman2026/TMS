<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery;

use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;

final class NotificationDeliveryAcceptanceComponent
{
    public function suites(): iterable
    {
        // <tms:suites>
        // </tms:suites>

        return [];
    }

    public function scenarios(): iterable
    {
        // <tms:scenarios>
        // </tms:scenarios>

        return [];
    }

    public function variants(string $scenarioKey): iterable
    {
        // <tms:variants>
        // </tms:variants>

        return [];
    }

    public function resolveScenario(
        string $suiteKey,
        string $scenarioKey,
        string $variantKey,
    ): ?AcceptanceScenario {
        // <tms:resolutions>
        // </tms:resolutions>

        return null;
    }

    private function metadata(): ScenarioMetadata
    {
        // Imported skeletons remain non-executable until an owner-local Pack adds behavior.
        return new ScenarioMetadata([], [], AutomationDisposition::NOT_IMPLEMENTED, EvidenceMode::METADATA_ONLY);
    }
}
