<?php

namespace App\Acceptance\Prerequisites\Data;

use App\Acceptance\Prerequisites\Enums\InputSensitivity;
use App\Acceptance\Prerequisites\Enums\InputSource;
use App\Acceptance\Prerequisites\Enums\InputType;
use InvalidArgumentException;

/** Immutable, value-free metadata for one declared operator input. */
final readonly class InputRequirement
{
    /** @var list<InputSource> */
    public array $allowedSources;

    /** @var list<string|int|bool>|null */
    public ?array $allowedValues;

    public ?int $listLimit;

    /**
     * @param  list<InputSource>  $allowedSources
     * @param  list<string|int|bool>|null  $allowedValues
     */
    public function __construct(
        public string $key,
        public InputType $type,
        public InputSensitivity $sensitivity = InputSensitivity::NON_SENSITIVE,
        public bool $required = true,
        public bool $approvalRelevant = false,
        array $allowedSources = [],
        ?array $allowedValues = null,
        public ?int $minimum = null,
        public ?int $maximum = null,
        ?int $listLimit = null,
    ) {
        self::assertKey($key);
        $expectedSource = $sensitivity === InputSensitivity::SECRET_REFERENCE
            ? InputSource::SECRET_REFERENCE : InputSource::LITERAL;
        $sources = $allowedSources === [] ? [$expectedSource] : array_values($allowedSources);
        if ($sources !== [$expectedSource]
            || ($type === InputType::SECRET_REFERENCE) !== ($sensitivity === InputSensitivity::SECRET_REFERENCE)) {
            throw new InvalidArgumentException('prerequisite_schema_invalid');
        }
        $this->allowedSources = $sources;

        if ($allowedValues !== null) {
            if (! in_array($type, [InputType::STRING, InputType::INTEGER, InputType::BOOLEAN], true)
                || $allowedValues === [] || ! array_is_list($allowedValues)) {
                throw new InvalidArgumentException('prerequisite_schema_invalid');
            }
            foreach ($allowedValues as $value) {
                if (! self::matchesType($type, $value)) {
                    throw new InvalidArgumentException('prerequisite_schema_invalid');
                }
            }
            if (count(array_unique(array_map(
                fn (string|int|bool $value): string => get_debug_type($value).':'.json_encode($value),
                $allowedValues,
            ), SORT_STRING)) !== count($allowedValues)) {
                throw new InvalidArgumentException('prerequisite_schema_invalid');
            }
        }
        if ($allowedValues !== null) {
            usort($allowedValues, fn (string|int|bool $left, string|int|bool $right): int => strcmp(
                get_debug_type($left).':'.json_encode($left),
                get_debug_type($right).':'.json_encode($right),
            ));
        }
        $this->allowedValues = $allowedValues === null ? null : array_values($allowedValues);

        if (($minimum !== null || $maximum !== null)
            && ! in_array($type, [InputType::STRING, InputType::INTEGER], true)) {
            throw new InvalidArgumentException('prerequisite_schema_invalid');
        }
        if ($type === InputType::STRING && (($minimum !== null && $minimum < 0)
            || ($maximum !== null && $maximum < 0)
            || ($minimum !== null && $minimum > PrerequisiteSchema::MAX_SCALAR_BYTES))
            || ($minimum !== null && $maximum !== null && $minimum > $maximum)) {
            throw new InvalidArgumentException('prerequisite_schema_invalid');
        }
        if ($allowedValues !== null) {
            foreach ($allowedValues as $value) {
                if ($type === InputType::STRING && (! mb_check_encoding($value, 'UTF-8')
                    || strlen($value) > PrerequisiteSchema::MAX_SCALAR_BYTES
                    || ($minimum !== null && mb_strlen($value, 'UTF-8') < $minimum)
                    || ($maximum !== null && mb_strlen($value, 'UTF-8') > $maximum))) {
                    throw new InvalidArgumentException('prerequisite_schema_invalid');
                }
                if ($type === InputType::INTEGER
                    && (($minimum !== null && $value < $minimum) || ($maximum !== null && $value > $maximum))) {
                    throw new InvalidArgumentException('prerequisite_schema_invalid');
                }
            }
        }
        if ($type === InputType::STRING_LIST) {
            if ($listLimit !== null && ($listLimit < 1 || $listLimit > PrerequisiteSchema::MAX_LIST_ITEMS)) {
                throw new InvalidArgumentException('prerequisite_schema_invalid');
            }
            $this->listLimit = $listLimit ?? PrerequisiteSchema::MAX_LIST_ITEMS;
        } elseif ($listLimit !== null) {
            throw new InvalidArgumentException('prerequisite_schema_invalid');
        } else {
            $this->listLimit = null;
        }
    }

    /** @return array<string, mixed> */
    public function semanticData(): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type->value,
            'sensitivity' => $this->sensitivity->value,
            'required' => $this->required,
            'approval_relevant' => $this->approvalRelevant,
            'allowed_sources' => array_map(fn (InputSource $source): string => $source->value, $this->allowedSources),
            'allowed_values' => $this->allowedValues,
            'minimum' => $this->minimum,
            'maximum' => $this->maximum,
            'list_limit' => $this->listLimit,
        ];
    }

    public static function assertKey(string $key): void
    {
        if (strlen($key) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1) {
            throw new InvalidArgumentException('prerequisite_schema_invalid');
        }
    }

    private static function matchesType(InputType $type, mixed $value): bool
    {
        return match ($type) {
            InputType::STRING => is_string($value),
            InputType::INTEGER => is_int($value),
            InputType::BOOLEAN => is_bool($value),
            default => false,
        };
    }
}
