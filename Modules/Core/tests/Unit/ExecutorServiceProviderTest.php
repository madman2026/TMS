<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use Modules\Core\Contracts\BrowserFactory;
use Modules\Core\Contracts\ExecutorRegistry;
use Modules\Core\Data\ExecutorCapability;
use Modules\Core\Services\BrowserAcceptanceExecutor;
use Modules\Core\Services\ExplicitExecutorRegistry;
use Modules\Core\Services\HttpAcceptanceExecutor;
use Modules\Core\Services\PlaywrightBrowserFactory;
use Tests\TestCase;

final class ExecutorServiceProviderTest extends TestCase
{
    public function test_provider_registers_one_explicit_registry_with_first_party_capabilities(): void
    {
        $first = $this->app->make(ExecutorRegistry::class);
        $second = $this->app->make(ExecutorRegistry::class);

        $this->assertSame($first, $second);
        $this->assertInstanceOf(ExplicitExecutorRegistry::class, $first);
        $this->assertInstanceOf(BrowserAcceptanceExecutor::class, $first->for(ExecutorCapability::browser()));
        $this->assertInstanceOf(HttpAcceptanceExecutor::class, $first->for(ExecutorCapability::http()));
        $this->assertNull($first->for(new ExecutorCapability('target-private')));
    }

    public function test_existing_browser_factory_binding_remains_available(): void
    {
        $this->assertInstanceOf(PlaywrightBrowserFactory::class, $this->app->make(BrowserFactory::class));
    }
}
