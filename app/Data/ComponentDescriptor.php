<?php

namespace App\Data;

/** Stable App-global Component identity. */
final readonly class ComponentDescriptor
{
    public function __construct(public string $key)
    {
        ScenarioDescriptor::assertKey($key);
    }
}
