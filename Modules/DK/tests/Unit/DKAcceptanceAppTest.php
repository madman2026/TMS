<?php

namespace Modules\DK\Tests\Unit;

use App\Contracts\AcceptanceComponentProvider;
use App\Contracts\AcceptanceCoverageProvider;
use Modules\DK\Acceptance\DKAcceptanceApp;
use PHPUnit\Framework\TestCase;

class DKAcceptanceAppTest extends TestCase
{
    public function test_it_exposes_an_empty_acceptance_app_contract(): void
    {
        $app = new DKAcceptanceApp;

        $this->assertInstanceOf(AcceptanceComponentProvider::class, $app);
        $this->assertInstanceOf(AcceptanceCoverageProvider::class, $app);
        $this->assertSame('dk', $app->key());
        $this->assertSame('v1', $app->catalogVersion());
        $this->assertSame([], iterator_to_array($app->components()));
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
