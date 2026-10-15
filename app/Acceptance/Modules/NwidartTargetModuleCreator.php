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
        [$definition, $componentContents] = $this->existingDefinitionForImport($moduleName);
        $this->assertMappings($mappings);
        [, $modulePath] = $this->validator->safePaths($definition);
        $app = $this->files->get($this->join($modulePath, 'app/Acceptance/'.$moduleName.'AcceptanceApp.php'));
        $components = $this->componentMap($app);
        $mappingRelative = 'app/Acceptance/Coverage/SourceCaseMappings.php';
        $mappingContent = $this->files->get($this->join($modulePath, $mappingRelative));
        $newFiles = [];
        $updates = [];
        $changeActions = [];
        $hierarchy = $this->hierarchyIndex($modulePath, $components, $componentContents);
        [$mappingLines, $sourceIds, $mappingIdentities] = $this->mappingIndex($mappingContent);
        $componentAdditions = [];

        foreach ($mappings as $mapping) {
            $mappingLine = trim($this->sourceMappingLine($mapping));
            $mappingExists = isset($mappingLines[$mappingLine]);
            $mappingIdentity = $mapping->executableIdentity();
            $mappingIdentityKey = $mappingIdentity === null ? null : implode("\0", $mappingIdentity);
            if (! $mappingExists && (isset($sourceIds[$mapping->sourceCaseId])
                || ($mappingIdentityKey !== null && isset($mappingIdentities[$mappingIdentityKey])))) {
                throw TargetModuleException::because('acceptance_source_mapping_duplicate');
            }
            if (! $mappingExists) {
                $mappingLines[$mappingLine] = true;
                $sourceIds[$mapping->sourceCaseId] = true;
                if ($mappingIdentityKey !== null) {
                    $mappingIdentities[$mappingIdentityKey] = true;
                }
            }
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
            $content = $this->migrateScenarioMetadataContract($content);
            $scenarioClass = Str::studly($mapping->scenarioKey);
            $scenarioFqcn = $definition->namespace.'\\Acceptance\\Components\\'.$componentClass
                .'\\Scenarios\\'.$scenarioClass;
            $componentContents[$componentRelative] = $content;
            $componentAdditions[$componentRelative]['suites'][] =
                "        yield new SuiteDescriptor('{$mapping->suiteKey}', '{$componentKey}');";
            $componentAdditions[$componentRelative]['scenarios'][] =
                "        yield new ScenarioDescriptor('{$mapping->scenarioKey}', '{$componentKey}', '{$mapping->suiteKey}', \$this->metadataFor('{$mapping->scenarioKey}'));";
            $componentAdditions[$componentRelative]['variants'][] =
                "        if (\$scenarioKey === '{$mapping->scenarioKey}') { yield new VariantDescriptor('{$mapping->variantKey}'); }";
            $componentAdditions[$componentRelative]['resolutions'][] =
                "        if (\$suiteKey === '{$mapping->suiteKey}' && \$scenarioKey === '{$mapping->scenarioKey}' && \$variantKey === '{$mapping->variantKey}') { return new \\{$scenarioFqcn}; }";

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

        $mappingContent = $this->mergeManagedLines($mappingContent, 'source-case-mappings', array_keys($mappingLines));
        foreach ($componentAdditions as $relative => $regions) {
            $content = $componentContents[$relative];
            foreach ($regions as $region => $lines) {
                $content = $this->mergeManagedLines($content, $region, $lines);
            }
            $componentContents[$relative] = $content;
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
        $candidateFiles = [...$newFiles, ...$updates];
        $validation = $this->validator->validatePath($definition, $modulePath, $candidateFiles);
        if (! $validation->valid || $validation->appKey !== $definition->appKey) {
            $issue = $validation->issues[0] ?? null;
            throw TargetModuleException::because($issue?->code ?? 'target_module_validation_failed');
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

    /** @return array{TargetModuleDefinition, array<string, string>} */
    private function existingDefinitionForImport(string $moduleName): array
    {
        if (! preg_match('/^[A-Z][A-Za-z0-9]{0,63}$/D', $moduleName)) {
            throw TargetModuleException::because('target_module_name_invalid');
        }
        $validation = $this->validator->validate($moduleName);
        if ($validation->valid && $validation->appKey !== null) {
            return [new TargetModuleDefinition($moduleName, $validation->appKey, $this->moduleNamespace()), []];
        }
        if ($validation->appKey === null) {
            throw TargetModuleException::because('target_module_validation_failed');
        }
        foreach ($validation->issues as $issue) {
            if (in_array($issue->code, [
                'target_module_descriptor_import_invalid',
                'target_module_managed_region_invalid',
            ], true)) {
                throw TargetModuleException::because($issue->code);
            }
            if (! in_array($issue->code, ['acceptance_hierarchy_invalid', 'acceptance_source_mapping_invalid'], true)) {
                throw TargetModuleException::because('target_module_validation_failed');
            }
        }

        $definition = new TargetModuleDefinition($moduleName, $validation->appKey, $this->moduleNamespace());
        [, $modulePath] = $this->validator->safePaths($definition);
        $app = $this->files->get($this->join($modulePath, 'app/Acceptance/'.$moduleName.'AcceptanceApp.php'));
        $componentContents = [];
        $migrated = false;
        foreach ($this->componentMap($app) as $componentClass) {
            $relative = 'app/Acceptance/Components/'.$componentClass.'/'.$componentClass.'AcceptanceComponent.php';
            $path = $this->join($modulePath, $relative);
            if (! $this->files->isFile($path)) {
                throw TargetModuleException::because('target_module_validation_failed');
            }
            $original = $this->files->get($path);
            $componentContents[$relative] = $this->migrateScenarioMetadataContract($original);
            $migrated = $migrated || $componentContents[$relative] !== $original;
        }
        $candidate = $this->validator->validatePath($definition, $modulePath, $componentContents);
        if (! $migrated || ! $candidate->valid || $candidate->appKey !== $definition->appKey) {
            throw TargetModuleException::because('target_module_validation_failed');
        }

        return [$definition, $componentContents];
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

    /**
     * @return array{array<string, true>, array<string, true>, array<string, true>}
     */
    private function mappingIndex(string $content): array
    {
        $region = $this->managedRegion($content, 'source-case-mappings');
        if ($region === null) {
            throw TargetModuleException::because('target_module_managed_region_invalid');
        }
        $lines = [];
        $sourceIds = [];
        $identities = [];
        foreach ($region['lines'] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $lines[$line] = true;
            if (preg_match("/sourceCaseId: '([A-Za-z0-9][A-Za-z0-9._:-]{0,127})'/", $line, $source) !== 1) {
                throw TargetModuleException::because('acceptance_source_mapping_invalid');
            }
            $sourceIds[$source[1]] = true;
            if (preg_match(
                "/componentKey: '([^']+)', suiteKey: '([^']+)', scenarioKey: '([^']+)', variantKey: '([^']+)'/",
                $line,
                $identity,
            ) === 1) {
                $identities[implode("\0", array_slice($identity, 1))] = true;
            }
        }

        return [$lines, $sourceIds, $identities];
    }

    /**
     * @param  array<string, string>  $components
     * @param  array<string, string>  $componentContents
     * @return array{suites: array<string, string>, scenarios: array<string, string>, variants: array<string, string>}
     */
    private function hierarchyIndex(string $modulePath, array $components, array $componentContents = []): array
    {
        $result = ['suites' => [], 'scenarios' => [], 'variants' => []];
        foreach ($components as $componentKey => $componentClass) {
            $path = $this->join($modulePath,
                'app/Acceptance/Components/'.$componentClass.'/'.$componentClass.'AcceptanceComponent.php');
            if (! $this->files->isFile($path)) {
                throw TargetModuleException::because('target_module_validation_failed');
            }
            $relative = 'app/Acceptance/Components/'.$componentClass.'/'.$componentClass.'AcceptanceComponent.php';
            $content = $componentContents[$relative] ?? $this->files->get($path);
            $suites = $this->managedRegion($content, 'suites');
            $scenarios = $this->managedRegion($content, 'scenarios');
            $variants = $this->managedRegion($content, 'variants');
            if ($suites === null || $scenarios === null || $variants === null) {
                throw TargetModuleException::because('target_module_managed_region_invalid');
            }
            foreach ($suites['lines'] as $line) {
                if (preg_match("/SuiteDescriptor\\('([^']+)', '([^']+)'\\)/", $line, $suite) === 1) {
                    $result['suites'][$suite[1]] = $suite[2];
                }
            }
            foreach ($scenarios['lines'] as $line) {
                if (preg_match("/ScenarioDescriptor\\('([^']+)', '([^']+)', '([^']+)'/", $line, $scenario) === 1) {
                    $result['scenarios'][$scenario[1]] = $scenario[2]."\0".$scenario[3];
                }
            }
            foreach ($variants['lines'] as $line) {
                if (preg_match("/\\\$scenarioKey === '([^']+)'\\) \\{ yield new VariantDescriptor\\('([^']+)'\\); \\}/", $line, $variant) === 1) {
                    $result['variants'][$variant[1]."\0".$variant[2]] = $componentKey;
                }
            }
        }

        return $result;
    }

    /** @param list<string> $newLines */
    private function mergeManagedLines(string $content, string $region, array $newLines): string
    {
        $managed = $this->managedRegion($content, $region);
        if ($managed === null) {
            throw TargetModuleException::because('target_module_managed_region_invalid');
        }
        $lines = array_map('trim', [...$managed['lines'], ...$newLines]);
        $lines = array_values(array_filter($lines, fn (string $line): bool => $line !== ''));
        $lines = array_values(array_unique($lines));
        sort($lines, SORT_STRING);
        $replacement = $managed['indent'].'// <tms:'.$region.'>'.$managed['newline'];
        if ($lines !== []) {
            $replacement .= $managed['indent'].implode(
                $managed['newline'].$managed['indent'],
                $lines,
            ).$managed['newline'];
        }
        $replacement .= $managed['indent'].'// </tms:'.$region.'>';

        return substr($content, 0, $managed['start'])
            .$replacement
            .substr($content, $managed['end']);
    }

    /**
     * @return array{start: int, end: int, indent: string, newline: string, lines: list<string>}|null
     */
    private function managedRegion(string $content, string $region): ?array
    {
        $opening = '// <tms:'.$region.'>';
        $closing = '// </tms:'.$region.'>';
        $length = strlen($content);
        $offset = 0;
        $start = null;
        $bodyStart = null;
        $end = null;
        $indent = '';
        $newline = null;
        $lines = [];

        while ($offset <= $length) {
            $lineFeed = strpos($content, "\n", $offset);
            $next = $lineFeed === false ? $length : $lineFeed + 1;
            $contentEnd = $lineFeed === false ? $length : $lineFeed;
            if ($contentEnd > $offset && $content[$contentEnd - 1] === "\r") {
                $contentEnd--;
            }
            $line = substr($content, $offset, $contentEnd - $offset);
            $trimmed = ltrim($line, " \t");
            $isMarker = (str_starts_with($trimmed, '// <tms:') || str_starts_with($trimmed, '// </tms:'))
                && str_ends_with($trimmed, '>');

            if ((str_contains($line, $opening) && $trimmed !== $opening)
                || (str_contains($line, $closing) && $trimmed !== $closing)) {
                return null;
            }
            if ($trimmed === $opening) {
                if ($start !== null || $end !== null || $lineFeed === false) {
                    return null;
                }
                $start = $offset;
                $bodyStart = $next;
                $indent = substr($line, 0, strlen($line) - strlen($trimmed));
                $newline = substr($content, $contentEnd, $next - $contentEnd);
            } elseif ($trimmed === $closing) {
                if ($start === null || $end !== null) {
                    return null;
                }
                if (substr($line, 0, strlen($line) - strlen($trimmed)) !== $indent) {
                    return null;
                }
                $end = $contentEnd;
            } elseif ($start !== null && $end === null) {
                if ($isMarker) {
                    return null;
                }
                $lines[] = $line;
            }

            if ($lineFeed === false) {
                break;
            }
            $offset = $next;
        }

        if ($start === null || $bodyStart === null || $end === null || $newline === null) {
            return null;
        }

        return compact('start', 'end', 'indent', 'newline', 'lines');
    }

    private function migrateScenarioMetadataContract(string $content): string
    {
        if (! str_contains($content, '$this->metadata()')) {
            return $content;
        }

        $legacyHook = 'private function metadata(): ScenarioMetadata';
        if (substr_count($content, $legacyHook) !== 1
            || str_contains($content, 'private function metadataFor(string $scenarioKey): ScenarioMetadata')) {
            throw TargetModuleException::because('target_module_path_collision');
        }

        $migrated = '';
        $offset = 0;
        $length = strlen($content);
        while ($offset < $length) {
            $lineFeed = strpos($content, "\n", $offset);
            $next = $lineFeed === false ? $length : $lineFeed + 1;
            $line = substr($content, $offset, $next - $offset);
            $lineEnding = str_ends_with($line, "\r\n") ? "\r\n" : (str_ends_with($line, "\n") ? "\n" : '');
            $body = $lineEnding === '' ? $line : substr($line, 0, -strlen($lineEnding));
            if (preg_match(
                "/^(?<indent>[ \\t]*)yield new ScenarioDescriptor\\('(?<scenario>[a-z0-9][a-z0-9._-]{0,63})', '(?<component>[a-z0-9][a-z0-9._-]{0,63})', '(?<suite>[a-z0-9][a-z0-9._-]{0,63})', \\\$this->metadata\\(\\)\\);$/D",
                $body,
                $match,
            ) === 1) {
                $body = $match['indent']
                    ."yield new ScenarioDescriptor('{$match['scenario']}', '{$match['component']}', '{$match['suite']}', "
                    ."\$this->metadataFor('{$match['scenario']}'));";
            }
            $migrated .= $body.$lineEnding;
            $offset = $next;
        }
        if (str_contains($migrated, '$this->metadata()')) {
            throw TargetModuleException::because('target_module_path_collision');
        }

        return str_replace(
            $legacyHook,
            'private function metadataFor(string $scenarioKey): ScenarioMetadata',
            $migrated,
        );
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
