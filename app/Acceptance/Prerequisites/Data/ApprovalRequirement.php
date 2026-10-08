<?php

namespace App\Acceptance\Prerequisites\Data;

use InvalidArgumentException;

/** Immutable workflow gate metadata; approval is not authorization evidence. */
final readonly class ApprovalRequirement
{
    /** @var list<string> */
    public array $invalidatedByInputKeys;

    /** @param list<string> $invalidatedByInputKeys */
    public function __construct(
        public string $scope,
        public bool $required = true,
        public int $ttlSeconds = PrerequisiteSchema::DEFAULT_APPROVAL_TTL_SECONDS,
        array $invalidatedByInputKeys = [],
    ) {
        InputRequirement::assertKey($scope);
        if ($ttlSeconds < 1 || ! array_is_list($invalidatedByInputKeys)) {
            throw new InvalidArgumentException('prerequisite_schema_invalid');
        }
        foreach ($invalidatedByInputKeys as $key) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('prerequisite_schema_invalid');
            }
            InputRequirement::assertKey($key);
        }
        if (count(array_unique($invalidatedByInputKeys, SORT_STRING)) !== count($invalidatedByInputKeys)) {
            throw new InvalidArgumentException('prerequisite_schema_invalid');
        }
        $this->invalidatedByInputKeys = array_values($invalidatedByInputKeys);
    }

    /** @return array<string, mixed> */
    public function semanticData(): array
    {
        return [
            'scope' => $this->scope,
            'required' => $this->required,
            'ttl_seconds' => $this->ttlSeconds,
            'invalidated_by_input_keys' => $this->invalidatedByInputKeys,
        ];
    }
}
