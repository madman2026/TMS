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
use Symfony\Component\Process\Process;
use Tests\TestCase;

class TargetModuleImportScaleTest extends TestCase
{
    private Filesystem $files;

    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->files = new Filesystem;
        $this->root = str_replace('\\', '/', sys_get_temp_dir()).'/tms-remediation-0006-scale-'.Str::uuid();
        $this->files->makeDirectory($this->root, 0755, true);
    }

    protected function tearDown(): void
    {
        $resolved = realpath($this->root);
        $temporary = realpath(sys_get_temp_dir());
        if ($resolved !== false && $temporary !== false
            && str_starts_with(
                strtolower(str_replace('\\', '/', $resolved)),
                strtolower(str_replace('\\', '/', $temporary)).'/tms-remediation-0006-scale-',
            )) {
            $this->files->deleteDirectory($resolved);
        }
        Mockery::close();
        parent::tearDown();
    }

    public function test_current_corpus_scale_dry_runs_applies_validates_and_replays_under_default_pcre_limit(): void
    {
        $creator = $this->creator();
        $creator->create(new TargetModuleDefinition('ScaleTarget', 'scale-app'), false);
        $creator->createComponent('ScaleTarget', 'notification-delivery', false);
        $mappings = $this->scaleMappings();
        $pcreLimit = ini_get('pcre.backtrack_limit');
        $before = $this->treeHashes('ScaleTarget');

        $dryRun = $creator->importScenarios('ScaleTarget', $mappings);

        $this->assertTrue($dryRun->dryRun);
        $this->assertSame(139, $dryRun->created());
        $this->assertSame(2, $dryRun->updated());
        $this->assertSame($before, $this->treeHashes('ScaleTarget'));
        $this->assertSame($pcreLimit, ini_get('pcre.backtrack_limit'));

        $startedAt = microtime(true);
        $applied = $creator->importScenarios('ScaleTarget', $mappings, false);
        $elapsedSeconds = microtime(true) - $startedAt;
        $componentPath = $this->root.'/ScaleTarget/app/Acceptance/Components/'
            .'NotificationDelivery/NotificationDeliveryAcceptanceComponent.php';
        $mappingPath = $this->root.'/ScaleTarget/app/Acceptance/Coverage/SourceCaseMappings.php';
        $component = $this->files->get($componentPath);
        $mappingSource = $this->files->get($mappingPath);
        $appliedHashes = [
            'component' => hash('sha256', $component),
            'mappings' => hash('sha256', $mappingSource),
        ];

        $this->assertFalse($applied->dryRun);
        $this->assertSame(139, $applied->created());
        $this->assertSame(2, $applied->updated());
        $this->assertSame(139, substr_count($component, 'new ScenarioDescriptor('));
        $this->assertSame(24668, substr_count($component, 'new VariantDescriptor('));
        $this->assertSame(24668, substr_count($mappingSource, 'new SourceCaseMapping('));
        $this->assertTrue($this->validator()->validate('ScaleTarget')->valid);
        $this->assertSame($pcreLimit, ini_get('pcre.backtrack_limit'));
        $this->assertGreaterThan(0.0, $elapsedSeconds);
        $this->assertGreaterThan(0, memory_get_peak_usage(true));
        $this->assertCanonicalGeneratedRegion($component, 'suites');
        $this->assertCanonicalGeneratedRegion($component, 'scenarios');
        $this->assertCanonicalGeneratedRegion($component, 'variants');
        $this->assertCanonicalGeneratedRegion($component, 'resolutions');
        $this->assertCanonicalGeneratedRegion($mappingSource, 'source-case-mappings');
        $replay = $creator->importScenarios('ScaleTarget', $mappings, false);

        $this->assertSame(0, $replay->created());
        $this->assertSame(0, $replay->updated());
        $this->assertSame(count($replay->changes), $replay->unchanged);
        $this->assertSame($appliedHashes, [
            'component' => hash_file('sha256', $componentPath),
            'mappings' => hash_file('sha256', $mappingPath),
        ]);
        $this->assertSame($pcreLimit, ini_get('pcre.backtrack_limit'));
        $this->assertPhpSyntaxValid($componentPath);
        $this->assertPhpSyntaxValid($mappingPath);
    }

    public function test_duplicate_identity_at_scale_boundary_fails_before_target_write(): void
    {
        $creator = $this->preparedCreator();
        $mapping = new SourceCaseMapping(
            'case-1',
            CoverageDisposition::AUTOMATED_FULL,
            'notification-delivery',
            'suite-001',
            'scenario-001',
            'variant-001',
        );
        $before = $this->treeHashes('ScaleTarget');

        try {
            $creator->importScenarios('ScaleTarget', [
                $mapping,
                new SourceCaseMapping(
                    'case-2',
                    CoverageDisposition::AUTOMATED_FULL,
                    'notification-delivery',
                    'suite-001',
                    'scenario-001',
                    'variant-001',
                ),
            ], false);
            $this->fail('Expected duplicate identity rejection.');
        } catch (TargetModuleException $exception) {
            $this->assertSame('acceptance_source_mapping_duplicate', $exception->errorCode);
        }

        $this->assertSame($before, $this->treeHashes('ScaleTarget'));
    }

    public function test_malformed_marker_at_scale_boundary_fails_before_target_write(): void
    {
        $creator = $this->preparedCreator();
        $path = $this->root.'/ScaleTarget/app/Acceptance/Coverage/SourceCaseMappings.php';
        $source = $this->files->get($path);
        $this->files->put(
            $path,
            str_replace('// <tms:source-case-mappings>', '// <tms:source-case-mappings>'.PHP_EOL
                .'            // <tms:nested>', $source),
        );
        $before = $this->treeHashes('ScaleTarget');

        try {
            $creator->importScenarios('ScaleTarget', [
                new SourceCaseMapping(
                    'case-1',
                    CoverageDisposition::AUTOMATED_FULL,
                    'notification-delivery',
                    'suite-001',
                    'scenario-001',
                    'variant-001',
                ),
            ], false);
            $this->fail('Expected malformed marker rejection.');
        } catch (TargetModuleException $exception) {
            $this->assertSame('target_module_managed_region_invalid', $exception->errorCode);
        }

        $this->assertSame($before, $this->treeHashes('ScaleTarget'));
    }

    /** @return list<SourceCaseMapping> */
    private function scaleMappings(): array
    {
        $mappings = [];
        for ($scenario = 1; $scenario <= 139; $scenario++) {
            $variantCount = $scenario <= 65 ? 178 : 177;
            for ($variant = 1; $variant <= $variantCount; $variant++) {
                $mappings[] = new SourceCaseMapping(
                    sprintf('case-%03d-%03d', $scenario, $variant),
                    CoverageDisposition::AUTOMATED_FULL,
                    'notification-delivery',
                    'suite-001',
                    sprintf('scenario-%03d', $scenario),
                    sprintf('variant-%03d', $variant),
                );
            }
        }

        $this->assertCount(24668, $mappings);

        return $mappings;
    }

    private function assertCanonicalGeneratedRegion(string $content, string $region): void
    {
        $opening = '// <tms:'.$region.'>';
        $closing = '// </tms:'.$region.'>';
        $inside = false;
        $lines = [];
        foreach (explode("\n", $content) as $line) {
            $line = str_ends_with($line, "\r") ? substr($line, 0, -1) : $line;
            if (trim($line) === $opening) {
                $inside = true;

                continue;
            }
            if (trim($line) === $closing) {
                break;
            }
            if ($inside) {
                $this->assertSame(rtrim($line, " \t"), $line);
                if ($line !== '') {
                    $lines[] = $line;
                }
            }
        }
        $sorted = $lines;
        sort($sorted, SORT_STRING);

        $this->assertSame($sorted, $lines);
        $this->assertSame(count($lines), count(array_unique($lines)));
    }

    private function assertPhpSyntaxValid(string $path): void
    {
        $process = new Process([PHP_BINARY, '-l', $path]);
        $process->run();

        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
    }

    private function preparedCreator(): NwidartTargetModuleCreator
    {
        $creator = $this->creator();
        $creator->create(new TargetModuleDefinition('ScaleTarget', 'scale-app'), false);
        $creator->createComponent('ScaleTarget', 'notification-delivery', false);

        return $creator;
    }

    private function creator(): NwidartTargetModuleCreator
    {
        $modules = $this->modules();
        $config = $this->config();

        return new NwidartTargetModuleCreator(
            $modules,
            $this->files,
            $config,
            new TargetModuleValidator($modules, $this->files, $config),
        );
    }

    private function validator(): TargetModuleValidator
    {
        return new TargetModuleValidator($this->modules(), $this->files, $this->config());
    }

    private function modules(): RepositoryInterface
    {
        $root = $this->root;
        $modules = Mockery::mock(RepositoryInterface::class);
        $modules->shouldReceive('getPath')->andReturn($root);
        $modules->shouldReceive('has')->andReturnUsing(
            fn (string $name): bool => $this->files->isDirectory($root.'/'.$name),
        );

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

    /** @return array<string, string> */
    private function treeHashes(string $moduleName): array
    {
        $hashes = [];
        foreach ($this->files->allFiles($this->root.'/'.$moduleName, true) as $file) {
            $hashes[str_replace('\\', '/', $file->getRelativePathname())] = hash_file('sha256', $file->getPathname());
        }
        ksort($hashes);

        return $hashes;
    }
}
