<?php

namespace App\Acceptance\Prerequisites\Contracts;

use App\Acceptance\Prerequisites\Data\PrerequisiteReferenceContext;

/** Acceptance App port for resolving one safe, opaque prerequisite reference. */
interface PrerequisiteReferenceResolver
{
    public function resolve(PrerequisiteReferenceContext $context): ?string;
}
