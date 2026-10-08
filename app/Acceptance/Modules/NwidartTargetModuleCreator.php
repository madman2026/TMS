<?php

namespace App\Acceptance\Modules;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Modules\Contracts\TargetModuleCreator;
use App\Acceptance\Operations\Data\FileChange;
use App\Acceptance\Operations\Data\ModuleChangeData;
use App\Data\ScenarioDescriptor;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Nwidart\Modules\Generators\FileGenerator;
use Nwidart\Modules\Support\Stub;
use Throwable;

/**
 * Uses Nwidart's path, stub, and file primitives without its command-coupled generator.
 */
final class NwidartTargetModuleCreator implements TargetModuleCreator
{
    public function __construct(
        private readonly RepositoryInterface $modules,
        private readonly Filesystem $files,
        private readonly ConfigRepository $config,
        private readonly TargetModuleValidator $validator,
    ) {}

    public function create(TargetModuleDefinition $definition, bool $dryRun = true): ModuleChangeData
    {
        [$root, $final] = $this->validator->safePaths($definition);
        if ($this->modules->has($definition->moduleName) || $this->files->exists($final)) {
            throw TargetModuleException::because('target_module_exists');
        }

        $contents = $this->appFiles($definition);
        $changes = array_map(
            fn (string $path): FileChange => new FileChange($path, 'create'),
            array_keys($contents),
        );
        if (! $dryRun) {
            $staging = $root.'/.tms-'.strtolower($definition->moduleName).'-'.Str::uuid();
            $this->assertStagingPath($root, $staging);
            try {
                $this->files->makeDirectory($staging, 0755, true);
                foreach ($contents as $relative => $content) {
                    $this->generateFile($staging, $relative, $content);
                }
                $validation = $this->validator->validatePath($definition, $staging);
                if (! $validation->valid || $validation->appKey !== $definition->appKey) {
                    throw TargetModuleException::because('target_module_generation_failed');
                }
                if (! $this->files->moveDirectory($staging, $final, false)) {
                    throw TargetModuleException::because('target_module_generation_failed');
                }
            } catch (TargetModuleException $exception) {
                $this->cleanupStaging($root, $staging);
                throw $exception;
            } catch (Throwable) {
                $this->cleanupStaging($root, $staging);
                throw TargetModuleException::because('target_module_generation_failed');
            }
        }

        return new ModuleChangeData('app', $definition->moduleName, $definition->appKey, null, $dryRun, $changes);
    }

    public function createComponent(string $moduleName, string $componentKey, bool $dryRun = true): ModuleChangeData
    {
        $definition = $this->existingDefinition($moduleName);
        $this->assertHierarchyKey($componentKey);
        $componentClass = Str::studly($componentKey);
        $appRelative = 'app/Acceptance/'.$moduleName.'AcceptanceApp.php';
        [, $modulePath] = $this->validator->safePaths($definition);
        $appPath = $this->join($modulePath, $appRelative);
        $app = $this->files->get($appPath);
        $componentRelative = 'app/Acceptance/Components/'.$componentClass.'/'.$componentClass.'AcceptanceComponent.php';
        $componentPath = $this->join($modulePath, $componentRelative);
        $component = $this->renderTms('AcceptanceComponent.stub', [
            'MODULE_NAMESPACE' => $definition->moduleNamespace,
            'MODULE_NAME' => $moduleName,
            'COMPONENT_CLASS' => $componentClass,
        ]);
        $entry = "            '".$componentKey."' => new \\".$definition->namespace.'\\Acceptance\\Components\\'
            .$componentClass.'\\'.$componentClass.'AcceptanceComponent,';
        $this->assertComponentCollision($app, $componentKey, $componentClass);
        $updatedApp = $this->mergeManagedLines($app, 'components', [$entry]);

        $changes = [
            new FileChange($appRelative, $updatedApp === $app ? 'unchanged' : 'update_managed_region'),
            new FileChange($componentRelative, $this->newFileAction($componentPath, $component)),
        ];
        if (! $dryRun) {
            $this->applyChanges(
                $modulePath,
                [$componentRelative => $component],
                $updatedApp === $app ? [] : [$appRelative => $updatedApp],
            );
        }

        return new ModuleChangeData('component', $moduleName, $definition->appKey, $componentKey, $dryRun, $changes);
    }

    public function importScenarios(string $moduleName, array $mappings, bool $dryRun = true): ModuleChangeData
    {
        $definition = $this->existingDefinition($moduleName);
        $this->assertMappings($mappings);
        [, $modulePath] = $this->validator->safePaths($definition);
        $app = $this->files->get($this->join($modulePath, 'app/Acceptance/'.$moduleName.'AcceptanceApp.php'));
        $components = $this->componentMap($app);
        $mappingRelative = 'app/Acceptance/Coverage/SourceCaseMappings.php';
        $mappingContent = $this->files->get($this->join($modulePath, $mappingRelative));
        $newFiles = [];
        $updates = [];
        $changeActions = [];
        $componentContents = [];
        $hierarchy = $this->hierarchyIndex($modulePath, $components);

        foreach ($mappings as $mapping) {
            $mappingExists = str_contains($mappingContent, trim($this->sourceMappingLine($mapping)));
            if (! $mappingExists) {
                $this->assertExistingMappingIdentity($mappingContent, $mapping);
            }
            $mappingContent = $this->mergeSourceMapping($mappingContent, $mapping);
            if (! $mapping->executable()) {
                continue;
            }
            $componentKey = $mapping->componentKey;
            if (! isset($components[$componentKey])) {
                throw TargetModuleException::because('acceptance_source_mapping_invalid');
            }
            $suiteOwner = $hierarchy['suites'][$mapping->suiteKey] ?? null;
            $scenarioOwner = $hierarchy['scenarios'][$mapping->scenarioKey] ?? null;
            $expectedScenarioOwner = $componentKey."\0".$mapping->suiteKey;
            if (($suiteOwner !== null && $suiteOwner !== $componentKey)
                || ($scenarioOwner !== null && $scenarioOwner !== $expectedScenarioOwner)
                || (! $mappingExists && isset($hierarchy['variants'][$mapping->scenarioKey."\0".$mapping->variantKey]))) {
                throw TargetModuleException::because('acceptance_source_mapping_duplicate');
            }
            $hierarchy['suites'][$mapping->suiteKey] = $componentKey;
            $hierarchy['scenarios'][$mapping->scenarioKey] = $expectedScenarioOwner;
            $hierarchy['variants'][$mapping->scenarioKey."\0".$mapping->variantKey] = $expectedScenarioOwner;
            $componentClass = $components[$componentKey];
            $componentRelative = 'app/Acceptance/Components/'.$componentClass.'/'.$componentClass.'AcceptanceComponent.php';
            $componentPath = $this->join($modulePath, $componentRelative);
            if (! $this->files->isFile($componentPath)) {
                throw TargetModuleException::because('acceptance_source_mapping_invalid');
            }
            $content = $componentContents[$componentRelative] ?? $this->files->get($componentPath);
            $scenarioClass = Str::studly($mapping->scenarioKey);
            $this->assertScenarioCollision($content, $mapping->scenarioKey, $scenarioClass, $mapping->suiteKey);
            $content = $this->mergeManagedLines($content, 'suites', [
                "        yield new SuiteDescriptor('{$mapping->suiteKey}', '{$componentKey}');",
            ]);
            $content = $this->mergeManagedLines($content, 'scenarios', [
                "        yield new ScenarioDescriptor('{$mapping->scenarioKey}', '{$componentKey}', '{$mapping->suiteKey}', \$this->metadata());",
            ]);
            $content = $this->mergeManagedLines($content, 'variants', [
                "        if (\$scenarioKey === '{$mapping->scenarioKey}') { yield new VariantDescriptor('{$mapping->variantKey}'); }",
            ]);
            $scenarioFqcn = $definition->namespace.'\\Acceptance\\Components\\'.$componentClass
                .'\\Scenarios\\'.$scenarioClass;
            $content = $this->mergeManagedLines($content, 'resolutions', [
                "        if (\$suiteKey === '{$mapping->suiteKey}' && \$scenarioKey === '{$mapping->scenarioKey}' && \$variantKey === '{$mapping->variantKey}') { return new \\{$scenarioFqcn}; }",
            ]);
            $componentContents[$componentRelative] = $content;

            $scenarioRelative = 'app/Acceptance/Components/'.$componentClass.'/Scenarios/'.$scenarioClass.'.php';
            $scenario = $this->renderTms('AcceptanceScenario.stub', [
                'MODULE_NAMESPACE' => $definition->moduleNamespace,
                'MODULE_NAME' => $moduleName,
                'COMPONENT_CLASS' => $componentClass,
                'SCENARIO_CLASS' => $scenarioClass,
                'SCENARIO_KEY' => $mapping->scenarioKey,
            ]);
            $newFiles[$scenarioRelative] = $scenario;
        }

        foreach ($componentContents as $relative => $content) {
            $original = $this->files->get($this->join($modulePath, $relative));
            if ($content !== $original) {
                $updates[$relative] = $content;
            }
            $changeActions[$relative] = $content === $original ? 'unchanged' : 'update_managed_region';
        }
        $originalMappings = $this->files->get($this->join($modulePath, $mappingRelative));
        if ($mappingContent !== $originalMappings) {
            $updates[$mappingRelative] = $mappingContent;
        }
        $changeActions[$mappingRelative] = $mappingContent === $originalMappings ? 'unchanged' : 'update_managed_region';
        foreach ($newFiles as $relative => $content) {
            $changeActions[$relative] = $this->newFileAction($this->join($modulePath, $relative), $content);
        }
        if (! $dryRun) {
            $this->applyChanges($modulePath, $newFiles, $updates);
        }

        $changes = [];
        foreach ($changeActions as $relative => $action) {
            $changes[] = new FileChange($relative, $action);
        }

        return new ModuleChangeData('scenarios', $moduleName, $definition->appKey, null, $dryRun, $changes);
    }

    /** @return array<string, string> */
    private function appFiles(TargetModuleDefinition $definition): array
    {
        $vendor = (string) $this->config->get('modules.composer.vendor', 'nwidart');
        $authorName = (string) $this->config->get('modules.composer.author.name', 'Nicolas Widart');
        $authorEmail = (string) $this->config->get('modules.composer.author.email', 'n.widart@gmail.com');
        if (! preg_match('/^[a-z0-9][a-z0-9_.-]{0,63}$/D', $vendor)
            || strlen($authorName) > 128
            || str_contains($authorName, '"')
            || str_contains($authorName, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $authorName)
            || strlen($authorEmail) > 254 || filter_var($authorEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw TargetModuleException::because('target_module_path_invalid');
        }
        $native = [
            'STUDLY_NAME' => $definition->moduleName,
            'LOWER_NAME' => strtolower($definition->moduleName),
            'MODULE_NAMESPACE' => $definition->moduleNamespace,
            'PROVIDER_NAMESPACE' => 'Providers',
            'VENDOR' => $vendor,
            'AUTHOR_NAME' => $authorName,
            'AUTHOR_EMAIL' => $authorEmail,
            'APP_FOLDER_NAME' => $this->appFolder(),
        ];

        return [
            'module.json' => (new Stub('/json.stub', $native))->render(),
            'composer.json' => (new Stub('/composer.stub', $native))->render(),
            'app/Providers/'.$definition->moduleName.'ServiceProvider.php' => $this->renderTms('TargetModuleServiceProvider.stub', [
                'MODULE_NAMESPACE' => $definition->moduleNamespace,
                'MODULE_NAME' => $definition->moduleName,
                'APP_KEY' => $definition->appKey,
            ]),
            'app/Acceptance/'.$definition->moduleName.'AcceptanceApp.php' => $this->renderTms('TargetAcceptanceApp.stub', [
                'MODULE_NAMESPACE' => $definition->moduleNamespace,
                'MODULE_NAME' => $definition->moduleName,
                'APP_KEY' => $definition->appKey,
            ]),
            'app/Acceptance/Shared/.gitkeep' => '',
            'app/Acceptance/Components/.gitkeep' => '',
            'app/Acceptance/Coverage/SourceCaseMappings.php' => $this->renderTms('SourceCaseMappings.stub', [
                'MODULE_NAMESPACE' => $definition->moduleNamespace,
                'MODULE_NAME' => $definition->moduleName,
            ]),
            'config/config.php' => (new Stub('/scaffold/config.stub', $native))->render(),
            'database/factories/.gitkeep' => '',
            'database/migrations/.gitkeep' => '',
            'database/seeders/'.$definition->moduleName.'DatabaseSeeder.php' => (new Stub('/seeder.stub', [
                'NAMESPACE' => $definition->namespace.'\\Database\\Seeders',
                'NAME' => $definition->moduleName.'DatabaseSeeder',
            ]))->render(),
            'tests/Feature/.gitkeep' => '',
            'tests/Unit/.gitkeep' => '',
        ];
    }

    private function existingDefinition(string $moduleName): TargetModuleDefinition
    {
        if (! preg_match('/^[A-Z][A-Za-z0-9]{0,63}$/D', $moduleName)) {
            throw TargetModuleException::because('target_module_name_invalid');
        }
        $validation = $this->validator->validate($moduleName);
        if (! $validation->valid || $validation->appKey === null) {
            throw TargetModuleException::because('target_module_validation_failed');
        }

        return new TargetModuleDefinition($moduleName, $validation->appKey, $this->moduleNamespace());
    }

    private function assertHierarchyKey(string $key): void
    {
        try {
            ScenarioDescriptor::assertKey($key, 'acceptance_hierarchy_key_invalid');
        } catch (Throwable) {
            throw TargetModuleException::because('acceptance_hierarchy_key_invalid');
        }
    }

    /** @param list<SourceCaseMapping> $mappings */
    private function assertMappings(array $mappings): void
    {
        if (! array_is_list($mappings)) {
            throw TargetModuleException::because('acceptance_source_mapping_invalid');
        }
        $sources = [];
        $identities = [];
        $replacements = [];
        foreach ($mappings as $mapping) {
            if (! $mapping instanceof SourceCaseMapping) {
                throw TargetModuleException::because('acceptance_source_mapping_invalid');
            }
            if (isset($sources[$mapping->sourceCaseId])) {
                throw TargetModuleException::because('acceptance_source_mapping_duplicate');
            }
            $sources[$mapping->sourceCaseId] = true;
            $identity = $mapping->executableIdentity();
            if ($identity !== null) {
                $key = implode("\0", $identity);
                if (isset($identities[$key])) {
                    throw TargetModuleException::because('acceptance_source_mapping_duplicate');
                }
                $identities[$key] = true;
            }
            $replacement = $mapping->replacementIdentity();
            if ($replacement !== null) {
                $key = implode("\0", $replacement);
                if (isset($replacements[$key])) {
                    throw TargetModuleException::because('acceptance_source_mapping_duplicate');
                }
                $replacements[$key] = true;
            }
        }
    }

    private function assertComponentCollision(string $app, string $key, string $class): void
    {
        preg_match_all("/'([^']+)' => new [^\\n]+\\\\([A-Za-z0-9]+)AcceptanceComponent,/", $app, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if (($match[1] === $key) !== ($match[2] === $class)) {
                throw TargetModuleException::because('target_module_path_collision');
            }
        }
    }

    private function assertScenarioCollision(string $component, string $key, string $class, string $suite): void
    {
        preg_match_all("/ScenarioDescriptor\\('([^']+)', '[^']+', '([^']+)'/", $component, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if ($match[1] === $key && $match[2] !== $suite) {
                throw TargetModuleException::because('target_module_path_collision');
            }
            if ($match[1] !== $key && Str::studly($match[1]) === $class) {
                throw TargetModuleException::because('target_module_path_collision');
            }
        }
    }

    /** @return array<string, string> */
    private function componentMap(string $app): array
    {
        preg_match_all("/'([^']+)' => new [^\\n]+\\\\([A-Za-z0-9]+)AcceptanceComponent,/", $app, $matches, PREG_SET_ORDER);
        $result = [];
        foreach ($matches as $match) {
            $result[$match[1]] = $match[2];
        }

        return $result;
    }

    private function mergeSourceMapping(string $content, SourceCaseMapping $mapping): string
    {
        preg_match_all("/sourceCaseId: '([^']+)'/", $content, $matches);
        $line = $this->sourceMappingLine($mapping);
        if (in_array($mapping->sourceCaseId, $matches[1] ?? [], true) && ! str_contains($content, trim($line))) {
            throw TargetModuleException::because('acceptance_source_mapping_duplicate');
        }

        return $this->mergeManagedLines($content, 'source-case-mappings', [$line]);
    }

    private function sourceMappingLine(SourceCaseMapping $mapping): string
    {
        return '            new SourceCaseMapping('.
            'sourceCaseId: '.$this->literal($mapping->sourceCaseId).', '.
            'disposition: CoverageDisposition::'.$mapping->disposition->name.', '.
            'componentKey: '.$this->literal($mapping->componentKey).', '.
            'suiteKey: '.$this->literal($mapping->suiteKey).', '.
            'scenarioKey: '.$this->literal($mapping->scenarioKey).', '.
            'variantKey: '.$this->literal($mapping->variantKey).', '.
            'replacementComponentKey: '.$this->literal($mapping->replacementComponentKey).', '.
            'replacementSuiteKey: '.$this->literal($mapping->replacementSuiteKey).', '.
            'replacementScenarioKey: '.$this->literal($mapping->replacementScenarioKey).', '.
            'replacementVariantKey: '.$this->literal($mapping->replacementVariantKey).', '.
            'reason: '.$this->literal($mapping->reason).', '.
            'coveredAssertions: '.$this->listLiteral($mapping->coveredAssertions).', '.
            'uncoveredAssertions: '.$this->listLiteral($mapping->uncoveredAssertions).'),';
    }

    private function assertExistingMappingIdentity(string $content, SourceCaseMapping $mapping): void
    {
        $identity = $mapping->executableIdentity();
        if ($identity !== null) {
            $fragment = 'componentKey: '.$this->literal($identity[0]).', suiteKey: '.$this->literal($identity[1])
                .', scenarioKey: '.$this->literal($identity[2]).', variantKey: '.$this->literal($identity[3]);
            if (str_contains($content, $fragment)) {
                throw TargetModuleException::because('acceptance_source_mapping_duplicate');
            }
        }
        $replacement = $mapping->replacementIdentity();
        if ($replacement !== null) {
            $fragment = 'replacementComponentKey: '.$this->literal($replacement[0])
                .', replacementSuiteKey: '.$this->literal($replacement[1])
                .', replacementScenarioKey: '.$this->literal($replacement[2])
                .', replacementVariantKey: '.$this->literal($replacement[3]);
            if (str_contains($content, $fragment)) {
                throw TargetModuleException::because('acceptance_source_mapping_duplicate');
            }
        }
    }

    /** @param array<string, string> $components @return array{suites: array<string, string>, scenarios: array<string, string>, variants: array<string, string>} */
    private function hierarchyIndex(string $modulePath, array $components): array
    {
        $result = ['suites' => [], 'scenarios' => [], 'variants' => []];
        foreach ($components as $componentKey => $componentClass) {
            $path = $this->join($modulePath,
                'app/Acceptance/Components/'.$componentClass.'/'.$componentClass.'AcceptanceComponent.php');
            if (! $this->files->isFile($path)) {
                throw TargetModuleException::because('target_module_validation_failed');
            }
            $content = $this->files->get($path);
            preg_match_all("/SuiteDescriptor\\('([^']+)', '([^']+)'\\)/", $content, $suites, PREG_SET_ORDER);
            foreach ($suites as $suite) {
                $result['suites'][$suite[1]] = $suite[2];
            }
            preg_match_all("/ScenarioDescriptor\\('([^']+)', '([^']+)', '([^']+)'/", $content, $scenarios, PREG_SET_ORDER);
            foreach ($scenarios as $scenario) {
                $result['scenarios'][$scenario[1]] = $scenario[2]."\0".$scenario[3];
            }
            preg_match_all("/\\\$scenarioKey === '([^']+)'\\) \\{ yield new VariantDescriptor\\('([^']+)'\\); \\}/", $content, $variants, PREG_SET_ORDER);
            foreach ($variants as $variant) {
                $result['variants'][$variant[1]."\0".$variant[2]] = $componentKey;
            }
        }

        return $result;
    }

    /** @param list<string> $newLines */
    private function mergeManagedLines(string $content, string $region, array $newLines): string
    {
        $pattern = '/^([ \t]*)\/\/ <tms:'.preg_quote($region, '/').'>\R(.*?)^\1\/\/ <\/tms:'
            .preg_quote($region, '/').'>/ms';
        if (! preg_match($pattern, $content, $match)) {
            throw TargetModuleException::because('target_module_path_collision');
        }
        $existing = preg_split('/\R/', trim($match[2])) ?: [];
        $lines = array_map('trim', [...$existing, ...$newLines]);
        $lines = array_values(array_filter($lines, fn (string $line): bool => $line !== ''));
        $lines = array_values(array_unique($lines));
        sort($lines, SORT_STRING);
        $replacement = $match[1].'// <tms:'.$region.'>'.PHP_EOL;
        if ($lines !== []) {
            $replacement .= $match[1].implode(PHP_EOL.$match[1], $lines).PHP_EOL;
        }
        $replacement .= $match[1].'// </tms:'.$region.'>';

        return preg_replace($pattern, $replacement, $content, 1) ?? throw TargetModuleException::because('target_module_path_collision');
    }

    /** @param array<string, string> $newFiles @param array<string, string> $updates */
    private function applyChanges(string $modulePath, array $newFiles, array $updates): void
    {
        $created = [];
        $originals = [];
        try {
            foreach ($newFiles as $relative => $content) {
                $path = $this->join($modulePath, $relative);
                $action = $this->newFileAction($path, $content);
                if ($action === 'create') {
                    $this->generateFile($modulePath, $relative, $content);
                    $created[] = $path;
                }
            }
            foreach ($updates as $relative => $content) {
                $path = $this->join($modulePath, $relative);
                $originals[$path] = $this->files->get($path);
                $this->files->replace($path, $content);
            }
        } catch (TargetModuleException $exception) {
            $this->rollbackChanges($created, $originals);
            throw $exception;
        } catch (Throwable) {
            $this->rollbackChanges($created, $originals);
            throw TargetModuleException::because('target_module_generation_failed');
        }
    }

    /** @param list<string> $created @param array<string, string> $originals */
    private function rollbackChanges(array $created, array $originals): void
    {
        foreach ($originals as $path => $content) {
            $this->files->replace($path, $content);
        }
        foreach (array_reverse($created) as $path) {
            $this->files->delete($path);
        }
    }

    private function newFileAction(string $path, string $content): string
    {
        if (! $this->files->exists($path)) {
            return 'create';
        }
        if ($this->files->isFile($path) && hash_equals(hash('sha256', $content), hash_file('sha256', $path))) {
            return 'unchanged';
        }

        throw TargetModuleException::because('target_module_path_collision');
    }

    private function generateFile(string $root, string $relative, string $content): void
    {
        $path = $this->join($root, $relative);
        $this->files->ensureDirectoryExists(dirname($path));
        $generator = (new FileGenerator($path, $content, $this->files))->withFileOverwrite(false);
        if ($generator->generate() === false) {
            throw TargetModuleException::because('target_module_generation_failed');
        }
    }

    /** @param array<string, string> $replacements */
    private function renderTms(string $stub, array $replacements): string
    {
        $previous = Stub::getBasePath();
        Stub::setBasePath(base_path('stubs/acceptance'));
        try {
            return (new Stub('/'.$stub, $replacements))->render();
        } finally {
            Stub::setBasePath($previous ?? base_path('vendor/nwidart/laravel-modules/src/Commands/stubs'));
        }
    }

    private function cleanupStaging(string $root, string $staging): void
    {
        $this->assertStagingPath($root, $staging);
        if ($this->files->isDirectory($staging)) {
            $this->files->deleteDirectory($staging);
        }
    }

    private function assertStagingPath(string $root, string $staging): void
    {
        $root = str_replace('\\', '/', rtrim($root, '/\\'));
        $staging = str_replace('\\', '/', rtrim($staging, '/\\'));
        $rootComparison = PHP_OS_FAMILY === 'Windows' ? strtolower($root) : $root;
        $stagingComparison = PHP_OS_FAMILY === 'Windows' ? strtolower($staging) : $staging;
        if (dirname($stagingComparison) !== $rootComparison || ! str_starts_with(basename($staging), '.tms-')) {
            throw TargetModuleException::because('target_module_path_invalid');
        }
    }

    private function moduleNamespace(): string
    {
        return (string) $this->config->get('modules.namespace', 'Modules');
    }

    private function appFolder(): string
    {
        $folder = (string) $this->config->get('modules.paths.app_folder', 'app/');
        if ($folder !== 'app/') {
            throw TargetModuleException::because('target_module_path_invalid');
        }

        return $folder;
    }

    private function join(string $root, string $relative): string
    {
        return rtrim($root, '/\\').DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    private function literal(?string $value): string
    {
        return $value === null ? 'null' : var_export($value, true);
    }

    /** @param list<string> $values */
    private function listLiteral(array $values): string
    {
        return '['.implode(', ', array_map(fn (string $value): string => var_export($value, true), $values)).']';
    }
}
