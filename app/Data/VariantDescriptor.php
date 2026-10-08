<?php

namespace App\Data;

use App\Acceptance\Prerequisites\Data\PrerequisiteSchema;

/** A variant carries one immutable code-owned prerequisite schema. */
final readonly class VariantDescriptor
{
    public PrerequisiteSchema $prerequisiteSchema;

    public function __construct(public string $key, ?PrerequisiteSchema $prerequisiteSchema = null)
    {
        ScenarioDescriptor::assertKey($key);
        $this->prerequisiteSchema = $prerequisiteSchema ?? PrerequisiteSchema::none();
    }
}
