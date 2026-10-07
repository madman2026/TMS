<?php

namespace App\Data;

use App\Exceptions\AcceptanceCatalogException;
use Modules\Core\Data\ScenarioMetadata;

/** Identity/classification only; target behavior and private state stay in App code. */
final readonly class ScenarioDescriptor
{
    public function __construct(public string $key, public ScenarioMetadata $metadata)
    {
        self::assertKey($key);
        self::classification($metadata);
    }

    public static function assertKey(mixed $key, string $errorCode = 'acceptance_catalog_invalid'): string
    {
        if (! is_string($key) || strlen($key) > 64
            || ! preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $key)) {
            throw AcceptanceCatalogException::because($errorCode);
        }

        return $key;
    }

    /** @return array{suites: list<string>, capabilities: list<string>, tags: list<string>, disposition: string, evidence_mode: string} */
    public static function classification(ScenarioMetadata $metadata): array
    {
        $result = [];

        foreach (['suites', 'capabilities', 'tags'] as $field) {
            if (count($metadata->$field) > 64) {
                throw AcceptanceCatalogException::because('acceptance_catalog_invalid');
            }

            $keys = [];
            foreach ($metadata->$field as $key) {
                $keys[] = self::assertKey($key);
            }
            if (count(array_unique($keys, SORT_STRING)) !== count($keys)) {
                throw AcceptanceCatalogException::because('acceptance_catalog_invalid');
            }
            sort($keys, SORT_STRING);
            $result[$field] = $keys;
        }

        $result['disposition'] = $metadata->disposition->value;
        $result['evidence_mode'] = $metadata->evidenceMode->value;

        return $result;
    }
}
