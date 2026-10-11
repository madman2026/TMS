<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit;

use App\Contracts\AcceptanceComponentProvider;
use App\Contracts\AcceptanceCoverageProvider;
use App\Data\ComponentDescriptor;
use Modules\DK\Acceptance\DKAcceptanceApp;
use PHPUnit\Framework\TestCase;

class DKAcceptanceAppTest extends TestCase
{
    public function test_it_exposes_the_imported_notification_delivery_catalog_as_non_executable_skeletons(): void
    {
        $app = new DKAcceptanceApp;

        $this->assertInstanceOf(AcceptanceComponentProvider::class, $app);
        $this->assertInstanceOf(AcceptanceCoverageProvider::class, $app);
        $this->assertSame('dk', $app->key());
        $this->assertSame('v3', $app->catalogVersion());
        $this->assertSame(
            ['notification-delivery'],
            array_map(
                static fn (ComponentDescriptor $component): string => $component->key,
                iterator_to_array($app->components()),
            ),
        );
        $this->assertCount(10, iterator_to_array($app->suites()));
        $this->assertCount(139, iterator_to_array($app->scenarios()));
        $this->assertSame([], iterator_to_array($app->variants('missing-scenario')));
        $this->assertCount(24668, iterator_to_array($app->sourceCaseMappings()));
        $this->assertNull($app->resolveScenario(
            'missing-component',
            'missing-suite',
            'missing-scenario',
            'missing-variant',
        ));
    }
}
