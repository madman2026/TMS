<?php

namespace App\Data;

/** A variant inherits its scenario metadata; no runtime inputs cross this boundary. */
final readonly class VariantDescriptor
{
    public function __construct(public string $key)
    {
        ScenarioDescriptor::assertKey($key);
    }
}
