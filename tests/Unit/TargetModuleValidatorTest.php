<?php

namespace Tests\Unit;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Modules\NwidartTargetModuleCreator;
use App\Acceptance\Modules\TargetModuleDefinition;
use App\Acceptance\Modules\TargetModuleValidator;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Mockery;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Tests\TestCase;

class TargetModuleValidatorTest extends TestCase
{
    private Filesystem $files;

    private string $root;

    private TargetModuleValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->files = new Filesystem;
        $this->root = str_replace('\\', '/', sys_get_temp_dir()).'/tms-pack-0010-validator-'.Str::uuid();
        $this->files->makeDirectory($this->root, 0755, true);
        $modules = $this->modules();
        $config = $this->config();
        $this->validator = new TargetModuleValidator($modules, $this->files, $config);
        (new NwidartTargetModuleCreator($modules, $this->files, $config, $this->validator))
            ->create(new TargetModuleDefinition('ExampleTarget', 'example-app'), false);
    }

    protected function tearDown(): void
    {
        $resolved = realpath($this->root);
        if ($resolved !== false && str_contains(str_replace('\\', '/', $resolved), '/tms-pack-0010-validator-')) {
            $this->files->deleteDirectory($resolved);
        }
        Mockery::close();
        parent::tearDown();
    }

    public function test_valid_layout_is_read_only_and_returns_deterministic_checked_paths(): void
    {
        $before = $this->treeHashes();
        $first = $this->validator->validate('ExampleTarget');
        $second = $this->validator->validate('ExampleTarget');

        $this->assertTrue($first->valid);
        $this->assertSame($first->checkedPaths, $second->checkedPaths);
        $this->assertSame($before, $this->treeHashes());
    }

    public function test_structural_manifest_provider_marker_and_forbidden_surface_issues_are_safe(): void
    {
        $this->files->delete($this->root.'/ExampleTarget/config/config.php');
        $this->files->put($this->root.'/ExampleTarget/module.json', '{invalid');
        $this->files->put($this->root.'/ExampleTarget/composer.json', '{invalid');
        $this->files->put($this->root.'/ExampleTarget/app/Providers/ExampleTargetServiceProvider.php', '<?php');
        $app = $this->files->get($this->root.'/ExampleTarget/app/Acceptance/ExampleTargetAcceptanceApp.php');
        $this->files->put(
            $this->root.'/ExampleTarget/app/Acceptance/ExampleTargetAcceptanceApp.php',
            str_replace(
                ['implements AcceptanceComponentProvider', '// </tms:components>'],
                ['', "            'missing' => new \\Modules\\ExampleTarget\\Acceptance\\Components\\Missing\\MissingAcceptanceComponent,"],
                $app,
            ),
        );
        $mappings = $this->files->get($this->root.'/ExampleTarget/app/Acceptance/Coverage/SourceCaseMappings.php');
        $this->files->put(
            $this->root.'/ExampleTarget/app/Acceptance/Coverage/SourceCaseMappings.php',
            str_replace('// </tms:source-case-mappings>', 'new SourceCaseMapping('.PHP_EOL.'            // </tms:source-case-mappings>', $mappings),
        );
        $this->files->makeDirectory($this->root.'/ExampleTarget/routes');

        $result = $this->validator->validate('ExampleTarget');
        $codes = array_map(fn ($issue): string => $issue->code, $result->issues);

        $this->assertFalse($result->valid);
        foreach ([
            'target_module_required_path_missing', 'target_module_manifest_invalid',
            'target_module_composer_invalid', 'target_module_provider_invalid', 'target_module_registration_missing',
            'target_module_acceptance_app_invalid',
            'target_module_managed_region_invalid', 'target_module_surface_forbidden',
            'acceptance_hierarchy_invalid', 'acceptance_source_mapping_invalid',
        ] as $code) {
            $this->assertContains($code, $codes);
        }
        $this->assertStringNotContainsString($this->root, print_r($result, true));
    }

    public function test_mapping_semantics_and_hierarchy_references_are_validated_without_loading_target_code(): void
    {
        $modules = $this->modules();
        $config = $this->config();
        $creator = new NwidartTargetModuleCreator($modules, $this->files, $config, $this->validator);
        $creator->createComponent('ExampleTarget', 'billing', false);
        $creator->importScenarios('ExampleTarget', [
            new SourceCaseMapping('case-1', CoverageDisposition::AUTOMATED_FULL,
                'billing', 'invoices', 'invoice-creates', 'default'),
        ], false);
        $path = $this->root.'/ExampleTarget/app/Acceptance/Coverage/SourceCaseMappings.php';
        $content = $this->files->get($path);
        $this->files->put($path, str_replace("componentKey: 'billing'", 'componentKey: null', $content));

        $result = $this->validator->validate('ExampleTarget');

        $this->assertFalse($result->valid);
        $this->assertContains('acceptance_source_mapping_invalid', array_column($result->issues, 'code'));
    }

    public function test_duplicate_hierarchy_entries_are_rejected_deterministically(): void
    {
        $modules = $this->modules();
        $config = $this->config();
        $creator = new NwidartTargetModuleCreator($modules, $this->files, $config, $this->validator);
        $creator->createComponent('ExampleTarget', 'billing', false);
        $creator->importScenarios('ExampleTarget', [
            new SourceCaseMapping('case-1', CoverageDisposition::AUTOMATED_FULL,
                'billing', 'invoices', 'invoice-creates', 'default'),
        ], false);
        $path = $this->root.'/ExampleTarget/app/Acceptance/Components/Billing/BillingAcceptanceComponent.php';
        $content = $this->files->get($path);
        $suite = "yield new SuiteDescriptor('invoices', 'billing');";
        $this->files->put($path, str_replace($suite, $suite.PHP_EOL.'        '.$suite, $content));

        $result = $this->validator->validate('ExampleTarget');

        $this->assertFalse($result->valid);
        $this->assertContains('acceptance_hierarchy_invalid', array_column($result->issues, 'code'));
    }

    private function modules(): RepositoryInterface
    {
        $root = $this->root;
        $modules = Mockery::mock(RepositoryInterface::class);
        $modules->shouldReceive('getPath')->andReturn($root);
        $modules->shouldReceive('has')->andReturnUsing(fn (string $name): bool => $this->files->isDirectory($root.'/'.$name));

        return $modules;
    }

    private function config(): ConfigRepository
    {
        return new ConfigRepository(['modules' => [
            'namespace' => 'Modules', 'paths' => ['app_folder' => 'app/'],
            'composer' => ['vendor' => 'example', 'author' => ['name' => 'Example', 'email' => 'example@example.test']],
        ]]);
    }

    /** @return array<string, string> */
    private function treeHashes(): array
    {
        $hashes = [];
        foreach ($this->files->allFiles($this->root.'/ExampleTarget', true) as $file) {
            $hashes[$file->getRelativePathname()] = hash_file('sha256', $file->getPathname());
        }
        ksort($hashes);

        return $hashes;
    }
}
