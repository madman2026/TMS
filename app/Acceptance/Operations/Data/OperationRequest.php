<?php

namespace App\Acceptance\Operations\Data;

/** Versioned machine input; validation belongs to the operation service. */
final readonly class OperationRequest
{
    public array $parameters;

    public function __construct(
        public string $operation,
        array $parameters = [],
        public int $version = 1,
        public ?string $correlationId = null,
    ) {
        $copy = [];
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $values = [];
                foreach ($value as $index => $entry) {
                    $values[$index] = $entry;
                }
                $value = $values;
            }
            // Detach references for the supported scalar and string-list shapes.
            $copy[$key] = $value;
        }
        $this->parameters = $copy;
    }
}
