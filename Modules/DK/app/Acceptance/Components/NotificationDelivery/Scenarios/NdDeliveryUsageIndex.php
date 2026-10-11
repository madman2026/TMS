<?php

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Scenarios;

use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;

final class NdDeliveryUsageIndex implements AcceptanceScenario
{
    public function key(): string
    {
        return 'nd-delivery-usage-index';
    }

    public function name(): string
    {
        return 'nd-delivery-usage-index';
    }

    public function metadata(): ScenarioMetadata
    {
        return new ScenarioMetadata([], [], AutomationDisposition::NOT_IMPLEMENTED, EvidenceMode::METADATA_ONLY);
    }

    public function steps(TestContext $context): iterable
    {
        return [];
    }
}
