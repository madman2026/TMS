<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit;

use App\Contracts\AcceptanceComponentProvider;
use App\Contracts\AcceptanceCoverageProvider;
use App\Data\ComponentDescriptor;
use Modules\Core\Enums\AutomationDisposition;
use Modules\DK\Acceptance\DKAcceptanceApp;
use PHPUnit\Framework\TestCase;

class DKAcceptanceAppTest extends TestCase
{
    public function test_it_exposes_one_bounded_executable_group_and_preserves_the_imported_catalog(): void
    {
        $app = new DKAcceptanceApp;

        $this->assertInstanceOf(AcceptanceComponentProvider::class, $app);
        $this->assertInstanceOf(AcceptanceCoverageProvider::class, $app);
        $this->assertSame('dk', $app->key());
        $this->assertSame('v4', $app->catalogVersion());
        $this->assertSame(
            ['notification-delivery'],
            array_map(
                static fn (ComponentDescriptor $component): string => $component->key,
                iterator_to_array($app->components()),
            ),
        );
        $this->assertCount(10, iterator_to_array($app->suites()));
        $this->assertCount(139, iterator_to_array($app->scenarios()));
        $descriptors = iterator_to_array($app->scenarios());
        $this->assertCount(1, array_filter(
            $descriptors,
            static fn ($descriptor): bool => $descriptor->metadata->disposition === AutomationDisposition::AUTOMATED,
        ));
        $this->assertCount(138, array_filter(
            $descriptors,
            static fn ($descriptor): bool => $descriptor->metadata->disposition === AutomationDisposition::NOT_IMPLEMENTED,
        ));
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
