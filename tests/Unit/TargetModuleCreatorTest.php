<?php

namespace Tests\Unit;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Modules\NwidartTargetModuleCreator;
use App\Acceptance\Modules\TargetModuleDefinition;
use App\Acceptance\Modules\TargetModuleException;
use App\Acceptance\Modules\TargetModuleValidator;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Mockery;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Tests\TestCase;

class TargetModuleCreatorTest extends TestCase
{
    private Filesystem $files;

    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->files = new Filesystem;
        $this->root = str_replace('\\', '/', sys_get_temp_dir()).'/tms-pack-0010-'.Str::uuid();
        $this->files->makeDirectory($this->root, 0755, true);
    }

    protected function tearDown(): void
    {
        $resolved = realpath($this->root);
        $temporary = realpath(sys_get_temp_dir());
        if ($resolved !== false && $temporary !== false
            && str_starts_with(strtolower(str_replace('\\', '/', $resolved)), strtolower(str_replace('\\', '/', $temporary)).'/tms-pack-0010-')) {
            $this->files->deleteDirectory($resolved);
        }
        Mockery::close();
        parent::tearDown();
    }

    public function test_dry_run_is_deterministic_and_does_not_create_a_module(): void
    {
        $creator = $this->creator();
        $definition = new TargetModuleDefinition('ExampleTarget', 'example-app');

        $first = $creator->create($definition);
        $second = $creator->create($definition);

        $this->assertTrue($first->dryRun);
        $this->assertEquals($first->changes, $second->changes);
        $this->assertSame(13, $first->created());
        $this->assertSame(13, $first->created);
        $this->assertDirectoryDoesNotExist($this->root.'/ExampleTarget');
    }

    public function test_creation_writes_the_exact_disabled_minimal_tree_and_validates_it(): void
    {
        $creator = $this->creator();
        $definition = new TargetModuleDefinition('ExampleTarget', 'example-app');

        $result = $creator->create($definition, false);
        $validation = $this->validator()->validate('ExampleTarget');

        $this->assertFalse($result->dryRun);
        $this->assertTrue($validation->valid);
        $this->assertSame('example-app', $validation->appKey);
        $this->assertFileExists($this->root.'/ExampleTarget/app/Acceptance/ExampleTargetAcceptanceApp.php');
        $app = $this->files->get($this->root.'/ExampleTarget/app/Acceptance/ExampleTargetAcceptanceApp.php');
        $this->assertStringContainsString('implements AcceptanceCoverageProvider', $app);
        $this->assertStringContainsString('yield from SourceCaseMappings::all();', $app);
        $this->assertFileExists($this->root.'/ExampleTarget/database/migrations/.gitkeep');
        $this->assertDirectoryDoesNotExist($this->root.'/ExampleTarget/routes');
        $this->assertFileDoesNotExist($this->root.'/modules_statuses.json');
    }

    public function test_component_and_mixed_mapping_import_are_managed_idempotently(): void
    {
        $creator = $this->creator();
        $creator->create(new TargetModuleDefinition('ExampleTarget', 'example-app'), false);
        $component = $creator->createComponent('ExampleTarget', 'billing', false);
        $mappings = $this->mappings();

        $import = $creator->importScenarios('ExampleTarget', $mappings, false);
        $again = $creator->importScenarios('ExampleTarget', $mappings, false);

        $this->assertSame('billing', $component->componentKey);
        $this->assertGreaterThan(0, $import->created());
        $this->assertSame(0, $again->created());
        $this->assertSame(0, $again->updated());
        $this->assertSame(count($again->changes), $again->unchanged);
        $this->assertFileExists($this->root.'/ExampleTarget/app/Acceptance/Components/Billing/Scenarios/InvoiceCreates.php');
        $this->assertFileDoesNotExist($this->root.'/ExampleTarget/app/Acceptance/Components/Billing/Scenarios/MergedCase.php');
        $componentSource = $this->files->get($this->root.'/ExampleTarget/app/Acceptance/Components/Billing/BillingAcceptanceComponent.php');
        $this->assertStringContainsString("yield new SuiteDescriptor('invoices', 'billing')", $componentSource);
        $this->assertStringContainsString('AutomationDisposition::NOT_IMPLEMENTED', $componentSource);
        $this->assertTrue($this->validator()->validate('ExampleTarget')->valid);
    }

    public function test_existing_module_and_derived_class_collisions_are_rejected_without_overwrite(): void
    {
        $creator = $this->creator();
        $creator->create(new TargetModuleDefinition('ExampleTarget', 'example-app'), false);
        $creator->createComponent('ExampleTarget', 'billing-api', false);
        $before = hash_file('sha256', $this->root.'/ExampleTarget/app/Acceptance/ExampleTargetAcceptanceApp.php');

        try {
            $creator->createComponent('ExampleTarget', 'billing_api', false);
            $this->fail('Expected a derived class collision.');
        } catch (TargetModuleException $exception) {
            $this->assertSame('target_module_path_collision', $exception->errorCode);
        }

        $this->assertSame($before, hash_file('sha256', $this->root.'/ExampleTarget/app/Acceptance/ExampleTargetAcceptanceApp.php'));
        $this->expectException(TargetModuleException::class);
        $creator->create(new TargetModuleDefinition('ExampleTarget', 'example-app'), false);
    }

    public function test_generation_failure_removes_only_its_verified_staging_directory(): void
    {
        $files = new class extends Filesystem
        {
            private int $writes = 0;

            public function put($path, $contents, $lock = false)
            {
                if (++$this->writes === 3) {
                    throw new \RuntimeException('example-sensitive-value');
                }

                return parent::put($path, $contents, $lock);
            }
        };
        $creator = $this->creator($files);

        try {
            $creator->create(new TargetModuleDefinition('BrokenTarget', 'broken-app'), false);
            $this->fail('Expected normalized generation failure.');
        } catch (TargetModuleException $exception) {
            $this->assertSame('target_module_generation_failed', $exception->errorCode);
        }

        $this->assertDirectoryDoesNotExist($this->root.'/BrokenTarget');
        $this->assertSame([], glob($this->root.'/.tms-*') ?: []);
    }

    public function test_a_later_import_cannot_reuse_an_existing_executable_identity(): void
    {
        $creator = $this->creator();
        $creator->create(new TargetModuleDefinition('ExampleTarget', 'example-app'), false);
        $creator->createComponent('ExampleTarget', 'billing', false);
        $creator->importScenarios('ExampleTarget', [
            new SourceCaseMapping('case-1', CoverageDisposition::AUTOMATED_FULL,
                'billing', 'invoices', 'invoice-creates', 'default'),
        ], false);

        $this->expectException(TargetModuleException::class);
        $this->expectExceptionMessage('acceptance_source_mapping_duplicate');
        $creator->importScenarios('ExampleTarget', [
            new SourceCaseMapping('case-2', CoverageDisposition::AUTOMATED_FULL,
                'billing', 'invoices', 'invoice-creates', 'default'),
        ], false);
    }

    public function test_merged_sources_can_share_one_replacement_in_same_and_later_imports(): void
    {
        $creator = $this->creator();
        $creator->create(new TargetModuleDefinition('ExampleTarget', 'example-app'), false);
        $creator->createComponent('ExampleTarget', 'billing', false);
        $creator->importScenarios('ExampleTarget', [
            new SourceCaseMapping('case-source', CoverageDisposition::AUTOMATED_FULL,
                'billing', 'invoices', 'invoice-creates', 'default'),
        ], false);

        $creator->importScenarios('ExampleTarget', [
            $this->mergedMapping('case-merged-1'),
            $this->mergedMapping('case-merged-2'),
        ], false);
        $creator->importScenarios('ExampleTarget', [
            $this->mergedMapping('case-merged-3'),
        ], false);

        $source = $this->files->get($this->root.'/ExampleTarget/app/Acceptance/Coverage/SourceCaseMappings.php');
        $this->assertSame(3, substr_count($source, "replacementScenarioKey: 'invoice-creates'"));
        $this->assertFileExists($this->root.'/ExampleTarget/app/Acceptance/Components/Billing/Scenarios/InvoiceCreates.php');
        $this->assertTrue($this->validator()->validate('ExampleTarget')->valid);
    }

    private function creator(?Filesystem $files = null): NwidartTargetModuleCreator
    {
        $files ??= $this->files;
        $modules = $this->modules($files);
        $config = $this->config();
        $validator = new TargetModuleValidator($modules, $files, $config);

        return new NwidartTargetModuleCreator($modules, $files, $config, $validator);
    }

    private function validator(): TargetModuleValidator
    {
        return new TargetModuleValidator($this->modules($this->files), $this->files, $this->config());
    }

    private function modules(Filesystem $files): RepositoryInterface
    {
        $root = $this->root;
        $modules = Mockery::mock(RepositoryInterface::class);
        $modules->shouldReceive('getPath')->andReturn($root);
        $modules->shouldReceive('has')->andReturnUsing(fn (string $name): bool => $files->isDirectory($root.'/'.$name));

        return $modules;
    }

    private function config(): ConfigRepository
    {
        return new ConfigRepository(['modules' => [
            'namespace' => 'Modules',
            'paths' => ['app_folder' => 'app/'],
            'composer' => [
                'vendor' => 'example-vendor',
                'author' => ['name' => 'Example Author', 'email' => 'author@example.test'],
            ],
        ]]);
    }

    /** @return list<SourceCaseMapping> */
    private function mappings(): array
    {
        return [
            new SourceCaseMapping('case-full', CoverageDisposition::AUTOMATED_FULL,
                'billing', 'invoices', 'invoice-creates', 'default'),
            new SourceCaseMapping('case-partial', CoverageDisposition::AUTOMATED_PARTIAL,
                'billing', 'invoices', 'invoice-creates', 'edge', reason: 'bounded coverage',
                coveredAssertions: ['creates'], uncoveredAssertions: ['external-side-effect']),
            new SourceCaseMapping('case-merged', CoverageDisposition::MERGED_EQUIVALENT,
                replacementComponentKey: 'billing', replacementSuiteKey: 'invoices',
                replacementScenarioKey: 'invoice-creates', replacementVariantKey: 'default',
                reason: 'equivalent', coveredAssertions: ['creates'], uncoveredAssertions: ['duplicate']),
            new SourceCaseMapping('case-excluded', CoverageDisposition::EXCLUDED_HUMAN_JUDGMENT,
                reason: 'subjective', uncoveredAssertions: ['visual-quality']),
        ];
    }

    private function mergedMapping(string $sourceCaseId): SourceCaseMapping
    {
        return new SourceCaseMapping(
            sourceCaseId: $sourceCaseId,
            disposition: CoverageDisposition::MERGED_EQUIVALENT,
            replacementComponentKey: 'billing',
            replacementSuiteKey: 'invoices',
            replacementScenarioKey: 'invoice-creates',
            replacementVariantKey: 'default',
            reason: 'same executable behavior',
            coveredAssertions: ['creates'],
            uncoveredAssertions: ['duplicate'],
        );
    }
}
