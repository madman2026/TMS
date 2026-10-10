<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Acceptance\Modules\TargetModuleValidator;
use App\Data\ComponentDescriptor;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceCatalog;
use Modules\DK\Acceptance\DKAcceptanceApp;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Tests\TestCase;

class DKModuleBootstrapTest extends TestCase
{
    public function test_dk_is_enabled_and_registers_its_acceptance_app_once(): void
    {
        $modules = $this->app->make(RepositoryInterface::class);
        $registry = $this->app->make(AcceptanceAppRegistry::class);

        $this->assertTrue($modules->has('DK'));
        $this->assertTrue($modules->isEnabled('DK'));
        $this->assertSame(['dk'], $registry->appKeys());
        $this->assertInstanceOf(DKAcceptanceApp::class, $registry->app('dk'));
    }

    public function test_dk_exposes_a_valid_component_only_catalog_and_module_definition(): void
    {
        $catalog = $this->app->make(AcceptanceCatalog::class);
        $validation = $this->app->make(TargetModuleValidator::class)->validate('DK');

        $this->assertSame(['dk'], $catalog->appKeys());
        $this->assertSame('v2', $catalog->version('dk'));
        $this->assertSame(
            ['notification-delivery'],
            array_map(
                static fn (ComponentDescriptor $component): string => $component->key,
                iterator_to_array($catalog->components('dk')),
            ),
        );
        $this->assertSame([], iterator_to_array($catalog->suites('dk')));
        $this->assertSame([], iterator_to_array($catalog->descriptors('dk')));
        $this->assertSame([], iterator_to_array($catalog->variants('dk', 'missing-scenario')));
        $this->assertSame('DK', $validation->moduleName);
        $this->assertSame('dk', $validation->appKey);
        $this->assertTrue($validation->valid);
        $this->assertSame([], $validation->issues);
        $this->assertNull($this->app->make(AcceptanceAppRegistry::class)->app('dk')?->resolveScenario(
            'missing-component',
            'missing-suite',
            'missing-scenario',
            'missing-variant',
        ));
    }
}
