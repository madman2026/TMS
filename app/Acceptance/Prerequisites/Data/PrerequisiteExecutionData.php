<?php

namespace App\Acceptance\Prerequisites\Data;

use InvalidArgumentException;

/** Internal in-memory prerequisite values for target resource adapters only. */
final readonly class PrerequisiteExecutionData
{
    /** @var array<string, string|int|bool|list<string>> */
    public array $nonSensitiveInputs;

    /** @var array<string, string> */
    public array $secretReferences;

    /**
     * @param  array<string, string|int|bool|list<string>>  $nonSensitiveInputs
     * @param  array<string, string>  $secretReferences
     */
    public function __construct(
        public ?string $requestId,
        public string $appKey,
        public string $componentKey,
        public string $suiteKey,
        public string $scenarioKey,
        public string $variantKey,
        public int $profileId,
        array $nonSensitiveInputs = [],
        array $secretReferences = [],
        public int $version = 1,
    ) {
        if ($version !== 1 || ($requestId !== null && ! self::isUuid($requestId)) || $profileId < 1) {
            throw new InvalidArgumentException('prerequisite_execution_data_invalid');
        }
        foreach ([$appKey, $componentKey, $suiteKey, $scenarioKey, $variantKey] as $key) {
            self::assertKey($key);
        }

        $safe = [];
        foreach ($nonSensitiveInputs as $key => $value) {
            self::assertKey($key);
            if (! self::validValue($value)) {
                throw new InvalidArgumentException('prerequisite_execution_data_invalid');
            }
            $safe[$key] = is_array($value) ? array_values($value) : $value;
        }
        $references = [];
        foreach ($secretReferences as $key => $reference) {
            self::assertKey($key);
            if (isset($safe[$key]) || ! is_string($reference)
                || preg_match('/^app-secret:\/\/'.preg_quote($appKey, '/').'\/[A-Za-z0-9._~-]{1,128}$/D', $reference) !== 1) {
                throw new InvalidArgumentException('prerequisite_execution_data_invalid');
            }
            $references[$key] = $reference;
        }
        ksort($safe, SORT_STRING);
        ksort($references, SORT_STRING);
        $this->nonSensitiveInputs = $safe;
        $this->secretReferences = $references;
    }

    private static function validValue(mixed $value): bool
    {
        if (is_string($value)) {
            return mb_check_encoding($value, 'UTF-8') && strlen($value) <= PrerequisiteSchema::MAX_SCALAR_BYTES;
        }
        if (is_int($value) || is_bool($value)) {
            return true;
        }
        if (! is_array($value) || ! array_is_list($value) || count($value) > PrerequisiteSchema::MAX_LIST_ITEMS) {
            return false;
        }
        $bytes = 0;
        foreach ($value as $item) {
            if (! is_string($item) || ! mb_check_encoding($item, 'UTF-8')) {
                return false;
            }
            $bytes += strlen($item);
        }

        return $bytes <= PrerequisiteSchema::MAX_LIST_BYTES;
    }

    private static function assertKey(string $key): void
    {
        if (strlen($key) > 64 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key) !== 1) {
            throw new InvalidArgumentException('prerequisite_execution_data_invalid');
        }
    }

    private static function isUuid(string $value): bool
    {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D', $value) === 1;
    }
}
