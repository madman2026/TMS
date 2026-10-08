<?php

namespace Tests\Unit;

use App\Contracts\AcceptanceComponentProvider;
use App\Exceptions\AcceptanceRegistryException;
use App\Services\AcceptanceAppRegistry;
use Modules\Core\Contracts\AcceptanceScenario;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AcceptanceAppRegistryTest extends TestCase
{
    public function test_it_registers_only_the_complete_provider_contract(): void
    {
        $provider = $this->provider('app-a');
        $registry = new AcceptanceAppRegistry;

        $registry->register($provider);

        $this->assertSame($provider, $registry->app('app-a'));
        $this->assertSame(['app-a'], $registry->appKeys());
        $this->assertNull($registry->app('missing'));
    }

    public function test_registration_is_identity_only_and_does_not_traverse_hierarchy(): void
    {
        $provider = $this->provider('app-a', true);
        $registry = new AcceptanceAppRegistry;

        $registry->register($provider);

        $this->assertSame(['app-a'], $registry->appKeys());
    }

    public function test_duplicate_and_invalid_app_keys_are_rejected_without_replacement(): void
    {
        $registry = new AcceptanceAppRegistry;
        $first = $this->provider('app-a');
        $registry->register($first);

        try {
            $registry->register($this->provider('app-a'));
            $this->fail('Expected duplicate registration to fail.');
        } catch (AcceptanceRegistryException $exception) {
            $this->assertSame('acceptance_registry_duplicate', $exception->errorCode);
        }
        $this->assertSame($first, $registry->app('app-a'));

        try {
            $registry->register($this->provider('Invalid'));
            $this->fail('Expected invalid registration to fail.');
        } catch (AcceptanceRegistryException $exception) {
            $this->assertSame('acceptance_registry_invalid', $exception->errorCode);
        }
    }

    #[DataProvider('invalidKeysProvider')]
    public function test_invalid_app_keys_are_rejected_with_a_fixed_safe_error(string $key): void
    {
        $registry = new AcceptanceAppRegistry;

        try {
            $registry->register($this->provider($key));
            $this->fail('Expected invalid app identity.');
        } catch (AcceptanceRegistryException $exception) {
            $this->assertSame('acceptance_registry_invalid', $exception->errorCode);
            $this->assertSame('The Acceptance Registry definition is invalid.', $exception->getMessage());
            $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
            $this->assertNull($exception->getPrevious());
            $this->assertSame([], $registry->appKeys());
        }
    }

    public function test_key_failure_is_normalized_without_partial_registration_or_exception_chain(): void
    {
        $provider = $this->provider('app-a');
        $provider->failKey = true;
        $registry = new AcceptanceAppRegistry;

        try {
            $registry->register($provider);
            $this->fail('Expected key failure.');
        } catch (AcceptanceRegistryException $exception) {
            $this->assertSame('acceptance_registry_invalid', $exception->errorCode);
            $this->assertNull($exception->getPrevious());
            $this->assertSame([], $registry->appKeys());
        }
    }

    public function test_registration_order_is_stable_and_lookup_does_not_mutate_it(): void
    {
        $registry = new AcceptanceAppRegistry;
        $first = $this->provider('app-b');
        $second = $this->provider('app-a');

        $registry->register($first);
        $registry->register($second);
        $this->assertSame($first, $registry->app('app-b'));
        $this->assertNull($registry->app('missing'));
        $this->assertSame(['app-b', 'app-a'], $registry->appKeys());
    }

    /** @return array<string, array{string}> */
    public static function invalidKeysProvider(): array
    {
        return [
            'empty' => [''],
            'uppercase' => ['Invalid'],
            'leading punctuation' => ['-invalid'],
            'whitespace' => ['invalid key'],
            'newline' => ["invalid\n"],
            'unicode' => ['مثال'],
            'too long' => [str_repeat('a', 65)],
            'sensitive-shaped input' => ['example-sensitive-value/secret'],
        ];
    }

    private function provider(string $key, bool $failOnTraversal = false): AcceptanceComponentProvider
    {
        return new class($key, $failOnTraversal) implements AcceptanceComponentProvider
        {
            public bool $failKey = false;

            public function __construct(
                private readonly string $appKey,
                private readonly bool $failOnTraversal,
            ) {}

            public function key(): string
            {
                if ($this->failKey) {
                    throw new \RuntimeException('example-sensitive-value');
                }

                return $this->appKey;
            }

            public function catalogVersion(): string
            {
                return 'v2';
            }

            public function components(): iterable
            {
                if ($this->failOnTraversal) {
                    throw new \RuntimeException('must stay lazy');
                }

                return [];
            }

            public function suites(): iterable
            {
                return [];
            }

            public function scenarios(): iterable
            {
                return [];
            }

            public function variants(string $scenarioKey): iterable
            {
                return [];
            }

            public function resolveScenario(
                string $componentKey,
                string $suiteKey,
                string $scenarioKey,
                string $variantKey,
            ): ?AcceptanceScenario {
                return null;
            }
        };
    }
}
