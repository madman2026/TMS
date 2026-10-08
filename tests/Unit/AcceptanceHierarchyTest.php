<?php

namespace Tests\Unit;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Modules\NwidartTargetModuleCreator;
use App\Acceptance\Modules\TargetModuleDefinition;
use App\Acceptance\Modules\TargetModuleValidator;
use App\Contracts\AcceptanceComponentProvider;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Mockery;
use Modules\Core\Enums\AutomationDisposition;
use Modules\GeneratedTarget\Acceptance\Coverage\SourceCaseMappings;
use Modules\GeneratedTarget\Acceptance\GeneratedTargetAcceptanceApp;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Tests\TestCase;

class AcceptanceHierarchyTest extends TestCase
{
    private Filesystem $files;

    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->files = new Filesystem;
        $this->root = str_replace('\\', '/', sys_get_temp_dir()).'/tms-pack-0010-hierarchy-'.Str::uuid();
        $this->files->makeDirectory($this->root, 0755, true);
    }

    protected function tearDown(): void
    {
        if ($this->files->isDirectory($this->root) && str_contains($this->root, '/tms-pack-0010-hierarchy-')) {
            $this->files->deleteDirectory($this->root);
        }
        Mockery::close();
        parent::tearDown();
    }

    public function test_generated_provider_implements_the_complete_exact_tuple_contract(): void
    {
        [$creator, $validator] = $this->services();
        $creator->create(new TargetModuleDefinition('GeneratedTarget', 'generated-app'), false);
        $creator->createComponent('GeneratedTarget', 'billing', false);
        $creator->importScenarios('GeneratedTarget', [
            new SourceCaseMapping('case-1', CoverageDisposition::AUTOMATED_FULL,
                'billing', 'invoices', 'invoice-creates', 'default'),
            new SourceCaseMapping('case-2', CoverageDisposition::AUTOMATED_FULL,
                'billing', 'invoices', 'invoice-deletes', 'default'),
        ], false);

        require_once $this->root.'/GeneratedTarget/app/Acceptance/Components/Billing/Scenarios/InvoiceCreates.php';
        require_once $this->root.'/GeneratedTarget/app/Acceptance/Components/Billing/Scenarios/InvoiceDeletes.php';
        require_once $this->root.'/GeneratedTarget/app/Acceptance/Components/Billing/BillingAcceptanceComponent.php';
        require_once $this->root.'/GeneratedTarget/app/Acceptance/GeneratedTargetAcceptanceApp.php';
        require_once $this->root.'/GeneratedTarget/app/Acceptance/Coverage/SourceCaseMappings.php';
        require_once $this->root.'/GeneratedTarget/app/Providers/GeneratedTargetServiceProvider.php';
        $provider = new GeneratedTargetAcceptanceApp;

        $this->assertInstanceOf(AcceptanceComponentProvider::class, $provider);
        $this->assertSame(['billing'], array_map(fn ($item): string => $item->key, iterator_to_array($provider->components())));
        $this->assertSame(['invoices'], array_map(fn ($item): string => $item->key, iterator_to_array($provider->suites())));
        $this->assertSame(['invoice-creates', 'invoice-deletes'], array_map(fn ($item): string => $item->key, iterator_to_array($provider->scenarios())));
        $this->assertSame(['default'], array_map(fn ($item): string => $item->key, iterator_to_array($provider->variants('invoice-creates'))));
        $this->assertSame(['default'], array_map(fn ($item): string => $item->key, iterator_to_array($provider->variants('invoice-deletes'))));
        $this->assertSame('none-v1', iterator_to_array($provider->variants('invoice-creates'))[0]->prerequisiteSchema->version);
        $this->assertSame([], iterator_to_array($provider->variants('invoice-creates'))[0]->prerequisiteSchema->inputs);
        $scenario = $provider->resolveScenario('billing', 'invoices', 'invoice-creates', 'default');
        $this->assertSame('invoice-creates', $scenario?->key());
        $this->assertSame(AutomationDisposition::NOT_IMPLEMENTED, $scenario?->metadata()->disposition);
        $this->assertNull($provider->resolveScenario('billing', 'wrong', 'invoice-creates', 'default'));
        $this->assertCount(2, SourceCaseMappings::all());
        $validation = $validator->validate('GeneratedTarget');
        $this->assertTrue($validation->valid, print_r($validation->issues, true));
    }

    /** @return array{NwidartTargetModuleCreator, TargetModuleValidator} */
    private function services(): array
    {
        $root = $this->root;
        $modules = Mockery::mock(RepositoryInterface::class);
        $modules->shouldReceive('getPath')->andReturn($root);
        $modules->shouldReceive('has')->andReturnUsing(fn (string $name): bool => $this->files->isDirectory($root.'/'.$name));
        $config = new ConfigRepository(['modules' => [
            'namespace' => 'Modules', 'paths' => ['app_folder' => 'app/'],
            'composer' => ['vendor' => 'example', 'author' => ['name' => 'Example', 'email' => 'example@example.test']],
        ]]);
        $validator = new TargetModuleValidator($modules, $this->files, $config);

        return [new NwidartTargetModuleCreator($modules, $this->files, $config, $validator), $validator];
    }
}
