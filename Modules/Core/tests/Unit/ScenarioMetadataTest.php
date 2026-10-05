<?php

namespace Modules\Core\Tests\Unit;

use Error;
use InvalidArgumentException;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class ScenarioMetadataTest extends TestCase
{
    public function test_enum_values_serialize_exactly(): void
    {
        $this->assertSame([
            'automated', 'manual-only', 'blocked', 'not-implemented',
        ], array_column(AutomationDisposition::cases(), 'value'));
        $this->assertSame([
            'metadata-only', 'non-sensitive-visual', 'sensitive-no-capture',
        ], array_column(EvidenceMode::cases(), 'value'));
        $this->assertSame(
            '["automated","manual-only","blocked","not-implemented"]',
            json_encode(AutomationDisposition::cases(), JSON_THROW_ON_ERROR),
        );
        $this->assertSame(
            '["metadata-only","non-sensitive-visual","sensitive-no-capture"]',
            json_encode(EvidenceMode::cases(), JSON_THROW_ON_ERROR),
        );
    }

    public function test_it_preserves_order_and_explicit_classification(): void
    {
        $metadata = new ScenarioMetadata(
            suites: ['suite-b', 'suite-a'],
            capabilities: ['capability.z', 'capability_a', '0'],
            tags: ['tag-2', 'tag-1'],
            disposition: AutomationDisposition::MANUAL_ONLY,
            evidenceMode: EvidenceMode::SENSITIVE_NO_CAPTURE,
        );

        $this->assertSame(['suite-b', 'suite-a'], $metadata->suites);
        $this->assertSame(['capability.z', 'capability_a', '0'], $metadata->capabilities);
        $this->assertSame(['tag-2', 'tag-1'], $metadata->tags);
        $this->assertSame(AutomationDisposition::MANUAL_ONLY, $metadata->disposition);
        $this->assertSame(EvidenceMode::SENSITIVE_NO_CAPTURE, $metadata->evidenceMode);
        $this->assertCount(5, (new ReflectionClass($metadata))->getProperties());
    }

    public function test_empty_collections_are_valid_without_implicit_classification(): void
    {
        $metadata = new ScenarioMetadata(
            suites: [],
            capabilities: [],
            tags: [],
            disposition: AutomationDisposition::NOT_IMPLEMENTED,
            evidenceMode: EvidenceMode::METADATA_ONLY,
        );

        $this->assertSame([], $metadata->suites);
        $this->assertSame([], $metadata->capabilities);
        $this->assertSame([], $metadata->tags);
        $this->assertSame(AutomationDisposition::NOT_IMPLEMENTED, $metadata->disposition);
        $this->assertSame(EvidenceMode::METADATA_ONLY, $metadata->evidenceMode);
        $this->assertSame(5, (new ReflectionClass($metadata))->getConstructor()->getNumberOfRequiredParameters());
    }

    public function test_keys_can_be_shared_across_separate_classification_lists(): void
    {
        $metadata = new ScenarioMetadata(
            suites: ['shared-key'],
            capabilities: ['shared-key'],
            tags: ['shared-key'],
            disposition: AutomationDisposition::AUTOMATED,
            evidenceMode: EvidenceMode::NON_SENSITIVE_VISUAL,
        );

        $this->assertSame(['shared-key'], $metadata->suites);
        $this->assertSame(['shared-key'], $metadata->capabilities);
        $this->assertSame(['shared-key'], $metadata->tags);
    }

    /**
     * @param  list<mixed>  $keys
     */
    #[DataProvider('invalidKeysProvider')]
    public function test_invalid_entries_in_each_list_are_rejected_with_a_fixed_safe_message(array $keys): void
    {
        foreach (['suites', 'capabilities', 'tags'] as $field) {
            $arguments = [
                'suites' => ['suite-a'],
                'capabilities' => ['capability-a'],
                'tags' => ['tag-a'],
                'disposition' => AutomationDisposition::AUTOMATED,
                'evidenceMode' => EvidenceMode::METADATA_ONLY,
            ];
            $arguments[$field] = $keys;

            try {
                new ScenarioMetadata(...$arguments);
                $this->fail('Expected invalid scenario metadata to be rejected.');
            } catch (InvalidArgumentException $exception) {
                $this->assertSame(InvalidArgumentException::class, $exception::class);
                $this->assertSame('Scenario metadata must contain unique, valid keys.', $exception->getMessage());
                $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
                $this->assertNull($exception->getPrevious());
            }
        }
    }

    /**
     * @return array<string, array{list<mixed>}>
     */
    public static function invalidKeysProvider(): array
    {
        return [
            'empty entry' => [['']],
            'duplicate' => [['example-sensitive-value', 'example-sensitive-value']],
            'non-adjacent duplicate' => [['key-a', 'key-b', 'key-a']],
            'null' => [[null]],
            'integer' => [[1]],
            'boolean' => [[true]],
            'float' => [[1.5]],
            'nested data' => [[['example-sensitive-value']]],
            'object' => [[new stdClass]],
            'executable closure' => [[static fn (): string => 'example-sensitive-value']],
            'uppercase' => [['Invalid-key']],
            'leading punctuation' => [['-invalid']],
            'whitespace' => [['example-sensitive-value invalid']],
            'trailing newline' => [["example-sensitive-value\n"]],
            'url' => [['https://example.test/example-sensitive-value']],
            'selector' => [['#example-sensitive-value']],
            'payload' => [['{"key":"example-sensitive-value"}']],
            'unicode' => [['مثال']],
        ];
    }

    #[DataProvider('immutablePropertiesProvider')]
    public function test_all_properties_are_immutable(string $property): void
    {
        $metadata = new ScenarioMetadata(
            suites: ['suite-a'],
            capabilities: ['capability-a'],
            tags: ['tag-a'],
            disposition: AutomationDisposition::AUTOMATED,
            evidenceMode: EvidenceMode::METADATA_ONLY,
        );
        $original = $metadata->$property;
        $this->assertTrue((new ReflectionClass($metadata))->isReadOnly());

        try {
            $metadata->$property = $original;
            $this->fail('Expected readonly metadata to reject assignment.');
        } catch (Error) {
            $this->assertSame($original, $metadata->$property);
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function immutablePropertiesProvider(): array
    {
        return [
            'suites' => ['suites'],
            'capabilities' => ['capabilities'],
            'tags' => ['tags'],
            'disposition' => ['disposition'],
            'evidence mode' => ['evidenceMode'],
        ];
    }

    public function test_array_entries_cannot_be_modified(): void
    {
        $metadata = new ScenarioMetadata(
            suites: ['suite-a'],
            capabilities: ['capability-a'],
            tags: ['tag-a'],
            disposition: AutomationDisposition::AUTOMATED,
            evidenceMode: EvidenceMode::METADATA_ONLY,
        );

        foreach (['suites', 'capabilities', 'tags'] as $property) {
            $original = $metadata->$property;

            try {
                $metadata->{$property}[0] = 'changed-key';
                $this->fail('Expected readonly metadata to reject array mutation.');
            } catch (Error) {
                $this->assertSame($original, $metadata->$property);
            }
        }
    }

    public function test_caller_arrays_and_referenced_entries_cannot_mutate_metadata(): void
    {
        $key = 'original-key';
        $keys = [&$key];
        $metadata = new ScenarioMetadata(
            suites: $keys,
            capabilities: $keys,
            tags: $keys,
            disposition: AutomationDisposition::AUTOMATED,
            evidenceMode: EvidenceMode::METADATA_ONLY,
        );

        $key = 'example-sensitive-value/invalid';
        $keys[] = 'later-key';

        $this->assertSame(['original-key'], $metadata->suites);
        $this->assertSame(['original-key'], $metadata->capabilities);
        $this->assertSame(['original-key'], $metadata->tags);
    }
}
