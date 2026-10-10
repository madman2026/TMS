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
    public function test_it_exposes_notification_delivery_without_executable_scenarios(): void
    {
        $app = new DKAcceptanceApp;

        $this->assertInstanceOf(AcceptanceComponentProvider::class, $app);
        $this->assertInstanceOf(AcceptanceCoverageProvider::class, $app);
        $this->assertSame('dk', $app->key());
        $this->assertSame('v2', $app->catalogVersion());
        $this->assertSame(
            ['notification-delivery'],
            array_map(
                static fn (ComponentDescriptor $component): string => $component->key,
                iterator_to_array($app->components()),
            ),
        );
        $this->assertSame([], iterator_to_array($app->suites()));
        $this->assertSame([], iterator_to_array($app->scenarios()));
        $this->assertSame([], iterator_to_array($app->variants('missing-scenario')));
        $this->assertSame([], iterator_to_array($app->sourceCaseMappings()));
        $this->assertNull($app->resolveScenario(
            'missing-component',
            'missing-suite',
            'missing-scenario',
            'missing-variant',
        ));
    }
}
