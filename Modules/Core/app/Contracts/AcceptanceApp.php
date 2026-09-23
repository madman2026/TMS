<?php

namespace Modules\Core\Contracts;

interface AcceptanceApp
{
    public function key(): string;

    /**
     * @return iterable<AcceptanceScenario>
     */
    public function scenarios(): iterable;
}
