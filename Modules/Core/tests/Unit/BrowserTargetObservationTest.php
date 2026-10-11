<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use Error;
use JsonSerializable;
use Modules\Core\Data\BrowserTargetObservation;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class BrowserTargetObservationTest extends TestCase
{
    public function test_bounded_semantic_facts_are_preserved_with_exact_types(): void
    {
        $facts = [
            'available' => true,
            'minimum' => -1_000_000_000,
            'maximum' => 1_000_000_000.0,
            'state' => 'ready-now',
            'reason' => null,
        ];

        $observation = new BrowserTargetObservation(str_repeat('a', 64), $facts);

        $this->assertSame(str_repeat('a', 64), $observation->key);
        $this->assertSame($facts, $observation->facts);
    }

    public function test_exact_fact_count_bounds_are_accepted(): void
    {
        $one = new BrowserTargetObservation('health', ['fact-1' => true]);
        $sixteenFacts = [];
        for ($index = 1; $index <= 16; $index++) {
            $sixteenFacts['fact-'.$index] = $index;
        }
        $sixteen = new BrowserTargetObservation('health', $sixteenFacts);

        $this->assertCount(1, $one->facts);
        $this->assertCount(16, $sixteen->facts);
    }

    #[DataProvider('invalidKeyProvider')]
    public function test_invalid_observation_keys_are_rejected_safely(string $key): void
    {
        try {
            new BrowserTargetObservation($key, ['available' => true]);
            $this->fail('Invalid observation key was accepted.');
        } catch (AcceptanceExecutionException $exception) {
            $this->assertSafeConfigurationException($exception);
        }
    }

    public static function invalidKeyProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'uppercase' => ['Health'];
        yield 'leading separator' => ['-health'];
        yield 'slash' => ['health/raw'];
        yield 'space' => ['health raw'];
        yield 'too long' => [str_repeat('a', 65)];
        yield 'invalid utf-8' => ["health\xFF"];
    }

    #[DataProvider('invalidFactsProvider')]
    public function test_invalid_fact_maps_and_values_are_rejected_safely(array $facts): void
    {
        try {
            new BrowserTargetObservation('health', $facts);
            $this->fail('Invalid target facts were accepted.');
        } catch (AcceptanceExecutionException $exception) {
            $this->assertSafeConfigurationException($exception);
        }
    }

    public static function invalidFactsProvider(): iterable
    {
        yield 'empty map' => [[]];
        yield 'list map' => [[true]];
        yield 'numeric key' => [[1 => true]];
        yield 'uppercase key' => [['Available' => true]];
        yield 'unsafe key' => [['raw/value' => true]];
        yield 'overlong key' => [[str_repeat('a', 65) => true]];
        yield 'seventeen facts' => [array_fill_keys(array_map(
            static fn (int $index): string => 'fact-'.$index,
            range(1, 17),
        ), true)];
        yield 'nested list' => [['raw' => ['value']]];
        yield 'nested map' => [['raw' => ['value' => true]]];
        yield 'object' => [['raw' => (object) ['value' => true]]];
        yield 'uppercase token' => [['state' => 'Ready']];
        yield 'arbitrary text' => [['state' => 'ready now']];
        yield 'unsafe token' => [['state' => 'https://target.invalid']];
        yield 'overlong token' => [['state' => str_repeat('a', 65)]];
        yield 'invalid utf-8 token' => [['state' => "ready\xFF"]];
        yield 'positive integer overflow' => [['count' => 1_000_000_001]];
        yield 'negative integer overflow' => [['count' => -1_000_000_001]];
        yield 'positive float overflow' => [['ratio' => 1_000_000_000.1]];
        yield 'negative float overflow' => [['ratio' => -1_000_000_000.1]];
        yield 'nan' => [['ratio' => NAN]];
        yield 'positive infinity' => [['ratio' => INF]];
        yield 'negative infinity' => [['ratio' => -INF]];
    }

    public function test_resources_are_rejected_safely(): void
    {
        $resource = fopen('php://memory', 'r');

        try {
            new BrowserTargetObservation('health', ['raw' => $resource]);
            $this->fail('Resource fact was accepted.');
        } catch (AcceptanceExecutionException $exception) {
            $this->assertSafeConfigurationException($exception);
        } finally {
            fclose($resource);
        }
    }

    public function test_caller_references_and_returned_copies_cannot_mutate_facts(): void
    {
        $state = 'ready';
        $input = ['state' => &$state];
        $observation = new BrowserTargetObservation('health', $input);
        $state = 'example-sensitive-value';
        $copy = $observation->facts;
        $copy['state'] = 'changed';

        $this->assertSame(['state' => 'ready'], $observation->facts);

        $this->expectException(Error::class);
        $observation->facts['state'] = 'changed';
    }

    public function test_observation_has_no_serialization_or_debug_export_contract(): void
    {
        $reflection = new ReflectionClass(BrowserTargetObservation::class);

        $this->assertTrue($reflection->isFinal());
        $this->assertTrue($reflection->isReadOnly());
        $this->assertFalse($reflection->implementsInterface(JsonSerializable::class));
        $this->assertFalse($reflection->hasMethod('toArray'));
        $this->assertFalse($reflection->hasMethod('__serialize'));
        $this->assertFalse($reflection->hasMethod('__debugInfo'));
    }

    private function assertSafeConfigurationException(AcceptanceExecutionException $exception): void
    {
        $this->assertSame('acceptance_configuration_invalid', $exception->errorCode);
        $this->assertFalse($exception->retryable);
        $this->assertSame('Acceptance run configuration is invalid.', $exception->getMessage());
        $this->assertNull($exception->getPrevious());
        $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
    }
}
