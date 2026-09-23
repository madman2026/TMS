<?php

namespace Modules\Core\Contracts;

interface AcceptanceScenario
{
    public function key(): string;

    public function name(): string;

    /**
     * @return iterable<StepResult>
     */
    public function steps(TestContext $context): iterable;
}
