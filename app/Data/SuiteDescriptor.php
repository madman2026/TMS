<?php

namespace App\Data;

/** Stable App-global Suite identity with its owning Component. */
final readonly class SuiteDescriptor
{
    public function __construct(public string $key, public string $componentKey)
    {
        ScenarioDescriptor::assertKey($key);
        ScenarioDescriptor::assertKey($componentKey);
    }
}
