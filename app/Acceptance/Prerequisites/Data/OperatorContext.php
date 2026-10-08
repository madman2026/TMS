<?php

namespace App\Acceptance\Prerequisites\Data;

use InvalidArgumentException;

/** Safe workflow actor context; it makes no authentication or authorization claim. */
final readonly class OperatorContext
{
    public function __construct(
        public string $actorType,
        public string $actorReference,
    ) {
        if (strlen($actorType) > 32 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $actorType) !== 1
            || strlen($actorReference) > 128 || preg_match('/^[A-Za-z0-9][A-Za-z0-9._~-]*$/D', $actorReference) !== 1) {
            throw new InvalidArgumentException('operator_context_invalid');
        }
    }
}
