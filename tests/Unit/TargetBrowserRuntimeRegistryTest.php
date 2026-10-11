<?php

namespace Tests\Unit;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Contracts\TargetBrowserRuntimeResolver;
use App\Acceptance\Targets\Data\TargetContext;
use App\Acceptance\Targets\TargetBrowserRuntimeRegistry;
use InvalidArgumentException;
use Mockery;
use Modules\Core\Contracts\BrowserTargetRuntime;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TargetBrowserRuntimeRegistryTest extends TestCase
{
    public function test_exact_app_key_returns_registered_resolver_without_fallback(): void
    {
        $runtime = Mockery::mock(BrowserTargetRuntime::class);
        $resolver = $this->resolver($runtime);
        $otherResolver = $this->resolver(Mockery::mock(BrowserTargetRuntime::class));
        $registry = new TargetBrowserRuntimeRegistry;
        $registry->register('app-a', $resolver);
        $registry->register('app-b', $otherResolver);

        $this->assertSame($resolver, $registry->for('app-a'));
        $this->assertSame($otherResolver, $registry->for('app-b'));
        $this->assertNull($registry->for('app-c'));
    }

    public function test_duplicate_app_key_is_rejected(): void
    {
        $registry = new TargetBrowserRuntimeRegistry;
        $registry->register('app-a', $this->resolver(Mockery::mock(BrowserTargetRuntime::class)));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('target_resource_registry_duplicate');

        $registry->register('app-a', $this->resolver(Mockery::mock(BrowserTargetRuntime::class)));
    }

    #[DataProvider('invalidKeys')]
    public function test_invalid_app_key_is_rejected(string $appKey): void
    {
        $registry = new TargetBrowserRuntimeRegistry;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('target_resource_registry_invalid');

        $registry->register($appKey, $this->resolver(Mockery::mock(BrowserTargetRuntime::class)));
    }

    /** @return iterable<string, array{string}> */
    public static function invalidKeys(): iterable
    {
        yield 'empty' => [''];
        yield 'uppercase' => ['App-a'];
        yield 'unsafe separator' => ['app/a'];
        yield 'too long' => [str_repeat('a', 65)];
    }

    private function resolver(BrowserTargetRuntime $runtime): TargetBrowserRuntimeResolver
    {
        return new class($runtime) implements TargetBrowserRuntimeResolver
        {
            public function __construct(private readonly BrowserTargetRuntime $runtime) {}

            public function resolve(
                TargetContext $context,
                PrerequisiteExecutionData $prerequisites,
            ): BrowserTargetRuntime {
                return $this->runtime;
            }
        };
    }
}
