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
use PHPUnit\Framework\Attributes\DataProvider;
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
                ['implements AcceptanceCoverageProvider', '// </tms:components>'],
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

    public function test_multiple_merged_sources_may_reference_one_valid_replacement(): void
    {
        $modules = $this->modules();
        $config = $this->config();
        $creator = new NwidartTargetModuleCreator($modules, $this->files, $config, $this->validator);
        $creator->createComponent('ExampleTarget', 'billing', false);
        $creator->importScenarios('ExampleTarget', [
            new SourceCaseMapping('case-source', CoverageDisposition::AUTOMATED_FULL,
                'billing', 'invoices', 'invoice-creates', 'default'),
            $this->mergedMapping('case-merged-1'),
            $this->mergedMapping('case-merged-2'),
        ], false);

        $result = $this->validator->validate('ExampleTarget');

        $this->assertTrue($result->valid);
        $this->assertSame([], $result->issues);
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

    public function test_scenario_metadata_key_must_match_the_descriptor_key(): void
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
        $this->files->put($path, str_replace(
            "metadataFor('invoice-creates')",
            "metadataFor('different-scenario')",
            $content,
        ));

        $result = $this->validator->validate('ExampleTarget');

        $this->assertFalse($result->valid);
        $this->assertContains('acceptance_hierarchy_invalid', array_column($result->issues, 'code'));
    }

    #[DataProvider('invalidDescriptorImports')]
    public function test_generated_descriptor_constructors_require_exact_unaliased_unique_imports(string $mutation): void
    {
        $creator = $this->creator();
        $creator->createComponent('ExampleTarget', 'billing', false);
        $creator->importScenarios('ExampleTarget', [
            new SourceCaseMapping('case-1', CoverageDisposition::AUTOMATED_FULL,
                'billing', 'invoices', 'invoice-creates', 'default'),
        ], false);
        $path = $this->componentPath();
        $content = $this->files->get($path);
        $import = 'use App\\Data\\SuiteDescriptor;';
        $withoutImport = str_replace([$import."\r\n", $import."\n"], '', $content);
        $content = match ($mutation) {
            'missing' => $withoutImport,
            'wrong-alias' => str_replace($import, 'use Vendor\\Contracts\\Suite as SuiteDescriptor;', $content),
            'duplicate' => str_replace($import, $import.PHP_EOL.$import, $content),
            'namespace-conflict' => str_replace(
                [$import, 'final class BillingAcceptanceComponent'],
                ['', 'final class SuiteDescriptor {}'.PHP_EOL.PHP_EOL.'final class BillingAcceptanceComponent'],
                $content,
            ),
        };
        $this->files->put($path, $content);
        $targetClass = 'Modules\\ExampleTarget\\Acceptance\\Components\\Billing\\BillingAcceptanceComponent';

        $result = $this->validator->validate('ExampleTarget');

        $this->assertFalse($result->valid);
        $this->assertSame(
            ['target_module_descriptor_import_invalid'],
            array_values(array_unique(array_column($result->issues, 'code'))),
        );
        $this->assertSame(
            'app/Acceptance/Components/Billing/BillingAcceptanceComponent.php',
            $result->issues[0]->path,
        );
        $this->assertFalse(class_exists($targetClass, false));
    }

    public function test_descriptor_constructor_text_in_comments_and_strings_does_not_require_imports(): void
    {
        $this->creator()->createComponent('ExampleTarget', 'billing', false);
        $path = $this->componentPath();
        $content = $this->files->get($path);
        $content = str_replace([
            'use App\\Data\\ScenarioDescriptor;'.PHP_EOL,
            'use App\\Data\\SuiteDescriptor;'.PHP_EOL,
            'use App\\Data\\VariantDescriptor;'.PHP_EOL,
        ], '', $content);
        $content = str_replace(
            'final class BillingAcceptanceComponent'.PHP_EOL.'{',
            'final class BillingAcceptanceComponent'.PHP_EOL.'{'.PHP_EOL
                ."    private const EXAMPLE = 'new SuiteDescriptor new ScenarioDescriptor new VariantDescriptor';".PHP_EOL
                .'    // new SuiteDescriptor; new ScenarioDescriptor; new VariantDescriptor;',
            $content,
        );

        $result = $this->validator->validatePath(
            new TargetModuleDefinition('ExampleTarget', 'example-app'),
            $this->root.'/ExampleTarget',
            ['app/Acceptance/Components/Billing/BillingAcceptanceComponent.php' => $content],
        );

        $this->assertTrue($result->valid);
        $this->assertSame([], $result->issues);
    }

    public function test_fully_qualified_descriptor_constructor_is_rejected_outside_the_exact_emitter_shape(): void
    {
        $creator = $this->creator();
        $creator->createComponent('ExampleTarget', 'billing', false);
        $creator->importScenarios('ExampleTarget', [
            new SourceCaseMapping('case-1', CoverageDisposition::AUTOMATED_FULL,
                'billing', 'invoices', 'invoice-creates', 'default'),
        ], false);
        $path = $this->componentPath();
        $content = str_replace(
            'yield new SuiteDescriptor(',
            'yield new \\App\\Data\\SuiteDescriptor(',
            $this->files->get($path),
        );
        $this->files->put($path, $content);

        $result = $this->validator->validate('ExampleTarget');
        $codes = array_column($result->issues, 'code');

        $this->assertFalse($result->valid);
        $this->assertContains('acceptance_hierarchy_invalid', $codes);
        $this->assertNotContains('target_module_descriptor_import_invalid', $codes);
    }

    public static function invalidDescriptorImports(): array
    {
        return [
            'missing import' => ['missing'],
            'wrong symbol alias' => ['wrong-alias'],
            'duplicate import' => ['duplicate'],
            'namespace declaration conflict' => ['namespace-conflict'],
        ];
    }

    private function creator(): NwidartTargetModuleCreator
    {
        $modules = $this->modules();
        $config = $this->config();

        return new NwidartTargetModuleCreator($modules, $this->files, $config, $this->validator);
    }

    private function componentPath(): string
    {
        return $this->root.'/ExampleTarget/app/Acceptance/Components/Billing/BillingAcceptanceComponent.php';
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
