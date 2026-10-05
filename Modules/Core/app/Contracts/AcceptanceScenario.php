<?php

namespace Modules\Core\Contracts;

use Modules\Core\Data\ScenarioMetadata;

interface AcceptanceScenario
{
    public function key(): string;

    public function name(): string;

    public function metadata(): ScenarioMetadata;

    /**
     * @return iterable<StepResult>
     */
    public function steps(TestContext $context): iterable;
}
