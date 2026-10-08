<?php

namespace App\Acceptance\Modules;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Operations\Data\TargetModuleValidationData;
use App\Acceptance\Operations\Data\ValidationIssue;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use JsonException;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Throwable;

/** Inspects generated source without loading a provider or executable scenario. */
final class TargetModuleValidator
{
    public function __construct(
        private readonly RepositoryInterface $modules,
        private readonly Filesystem $files,
        private readonly ConfigRepository $config,
    ) {}

    public function validate(string $moduleName): TargetModuleValidationData
    {
        $definition = new TargetModuleDefinition($moduleName, 'placeholder', $this->moduleNamespace());
        [, $path] = $this->safePaths($definition);
        if (! $this->files->isDirectory($path)) {
            throw TargetModuleException::because('target_module_not_found');
        }

        return $this->validatePath($definition, $path);
    }

    public function validatePath(TargetModuleDefinition $definition, string $path): TargetModuleValidationData
    {
        [$root, $path] = $this->safePaths($definition, $path);
        $issues = [];
        $checked = [];
        $requiredFiles = [
            'module.json', 'composer.json',
            'app/Providers/'.$definition->moduleName.'ServiceProvider.php',
            'app/Acceptance/'.$definition->moduleName.'AcceptanceApp.php',
            'app/Acceptance/Shared/.gitkeep', 'app/Acceptance/Components/.gitkeep',
            'app/Acceptance/Coverage/SourceCaseMappings.php', 'config/config.php',
            'database/factories/.gitkeep', 'database/migrations/.gitkeep',
            'database/seeders/'.$definition->moduleName.'DatabaseSeeder.php',
            'tests/Feature/.gitkeep', 'tests/Unit/.gitkeep',
        ];
        foreach ($requiredFiles as $relative) {
            $checked[] = $relative;
            if (! $this->files->isFile($this->join($path, $relative))) {
                $issues[] = new ValidationIssue('target_module_required_path_missing', $relative);
            }
        }

        $appKey = null;
        $manifest = $this->json($path, 'module.json', 'target_module_manifest_invalid', $issues);
        if ($manifest !== null && ($manifest['name'] ?? null) !== $definition->moduleName) {
            $issues[] = new ValidationIssue('target_module_manifest_invalid', 'module.json');
        }
        if ($manifest !== null && (($manifest['alias'] ?? null) !== $definition->moduleAlias
            || ($manifest['providers'] ?? null) !== [$definition->namespace.'\\Providers\\'.$definition->moduleName.'ServiceProvider'])) {
            $issues[] = new ValidationIssue('target_module_manifest_invalid', 'module.json');
        }

        $composer = $this->json($path, 'composer.json', 'target_module_composer_invalid', $issues);
        $autoload = $composer['autoload']['psr-4'] ?? null;
        if ($composer !== null && (! is_array($autoload)
            || ($autoload[$definition->namespace.'\\'] ?? null) !== $this->appFolder())) {
            $issues[] = new ValidationIssue('target_module_composer_invalid', 'composer.json');
        }

        $providerRelative = 'app/Providers/'.$definition->moduleName.'ServiceProvider.php';
        $provider = $this->read($path, $providerRelative);
        if ($provider !== null && (! str_contains($provider, 'final class '.$definition->moduleName.'ServiceProvider')
            || ! str_contains($provider, 'AcceptanceAppRegistry')
            || ! str_contains($provider, 'new '.$definition->moduleName.'AcceptanceApp'))) {
            $issues[] = new ValidationIssue('target_module_provider_invalid', $providerRelative);
        }
        if ($provider !== null && ! str_contains($provider, 'registry->register')) {
            $issues[] = new ValidationIssue('target_module_registration_missing', $providerRelative);
        }

        $appRelative = 'app/Acceptance/'.$definition->moduleName.'AcceptanceApp.php';
        $app = $this->read($path, $appRelative);
        if ($app !== null) {
            if (! preg_match("/return '([a-z0-9][a-z0-9._-]{0,63})';/D", $app, $matches)
                || ! str_contains($app, 'implements AcceptanceComponentProvider')) {
                $issues[] = new ValidationIssue('target_module_acceptance_app_invalid', $appRelative);
            } else {
                $appKey = $matches[1];
            }
            if (! $this->markersValid($app, ['components'])) {
                $issues[] = new ValidationIssue('target_module_managed_region_invalid', $appRelative);
            }
        }

        $mappingRelative = 'app/Acceptance/Coverage/SourceCaseMappings.php';
        $mapping = $this->read($path, $mappingRelative);
        if ($mapping !== null && ! $this->markersValid($mapping, ['source-case-mappings'])) {
            $issues[] = new ValidationIssue('target_module_managed_region_invalid', $mappingRelative);
        }

        $components = $this->join($path, 'app/Acceptance/Components');
        $componentOwners = $app === null ? [] : $this->componentEntries($app);
        if ($componentOwners === null) {
            $issues[] = new ValidationIssue('acceptance_hierarchy_invalid', $appRelative);
            $componentOwners = [];
        }
        $suiteOwners = [];
        $scenarioOwners = [];
        $variantOwners = [];
        if ($this->files->isDirectory($components)) {
            foreach ($this->files->allFiles($components) as $file) {
                $relative = $this->relative($path, $file->getPathname());
                $checked[] = $relative;
                if (str_ends_with($relative, 'AcceptanceComponent.php')
                    && ! $this->markersValid($file->getContents(), ['suites', 'scenarios', 'variants', 'resolutions'])) {
                    $issues[] = new ValidationIssue('target_module_managed_region_invalid', $relative);
                }
                if (str_ends_with($relative, 'AcceptanceComponent.php')) {
                    $class = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                    $componentKey = array_search(substr($class, 0, -strlen('AcceptanceComponent')), $componentOwners, true);
                    if (! is_string($componentKey)
                        || ! $this->hierarchyValid($file->getContents(), $componentKey, $suiteOwners, $scenarioOwners, $variantOwners)) {
                        $issues[] = new ValidationIssue('acceptance_hierarchy_invalid', $relative);
                    }
                }
            }
        }
        foreach ($componentOwners as $class) {
            $relative = 'app/Acceptance/Components/'.$class.'/'.$class.'AcceptanceComponent.php';
            if (! $this->files->isFile($this->join($path, $relative))) {
                $issues[] = new ValidationIssue('acceptance_hierarchy_invalid', $relative);
            }
        }

        if ($mapping !== null && ! $this->sourceMappingsValid(
            $mapping,
            $componentOwners,
            $suiteOwners,
            $scenarioOwners,
            $variantOwners,
        )) {
            $issues[] = new ValidationIssue('acceptance_source_mapping_invalid', $mappingRelative);
        }

        foreach (['routes', 'resources', 'app/Http', 'app/Models'] as $forbidden) {
            if ($this->files->exists($this->join($path, $forbidden))) {
                $issues[] = new ValidationIssue('target_module_surface_forbidden', $forbidden);
            }
        }
        $migrations = $this->join($path, 'database/migrations');
        if ($this->files->isDirectory($migrations)) {
            foreach ($this->files->files($migrations, true) as $file) {
                if ($file->getFilename() !== '.gitkeep') {
                    $issues[] = new ValidationIssue('target_module_surface_forbidden', $this->relative($path, $file->getPathname()));
                }
            }
        }

        if (! $this->contained($root, $path)) {
            throw TargetModuleException::because('target_module_path_invalid');
        }

        return new TargetModuleValidationData(
            $definition->moduleName,
            $appKey,
            $issues === [],
            $issues,
            $checked,
        );
    }

    /** @return array{string, string} */
    public function safePaths(TargetModuleDefinition $definition, ?string $candidate = null): array
    {
        $root = realpath($this->modules->getPath());
        if ($root === false || ! $this->files->isDirectory($root)) {
            throw TargetModuleException::because('target_module_path_invalid');
        }
        $root = $this->normalize($root);
        $candidatePath = $candidate === null
            ? rtrim($root, '/').'/'.$definition->moduleName
            : (realpath($candidate) ?: $candidate);
        $path = $this->normalize($candidatePath);
        if (! $this->contained($root, $path)) {
            throw TargetModuleException::because('target_module_path_invalid');
        }

        return [$root, $path];
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

    private function json(string $root, string $relative, string $code, array &$issues): ?array
    {
        $content = $this->read($root, $relative);
        if ($content === null) {
            return null;
        }
        try {
            $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
            if (is_array($decoded)) {
                return $decoded;
            }
        } catch (JsonException) {
        }
        $issues[] = new ValidationIssue($code, $relative);

        return null;
    }

    private function read(string $root, string $relative): ?string
    {
        $path = $this->join($root, $relative);

        try {
            return $this->files->isFile($path) ? $this->files->get($path) : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** @param list<string> $regions */
    private function markersValid(string $content, array $regions): bool
    {
        foreach ($regions as $region) {
            if (substr_count($content, '// <tms:'.$region.'>') !== 1
                || substr_count($content, '// </tms:'.$region.'>') !== 1
                || strpos($content, '// <tms:'.$region.'>') > strpos($content, '// </tms:'.$region.'>')) {
                return false;
            }
        }

        return true;
    }

    /** @return array<string, string>|null */
    private function componentEntries(string $app): ?array
    {
        preg_match_all("/'([^']+)' => new \\\\[^\\n]+\\\\([A-Za-z0-9]+)AcceptanceComponent,/", $app, $matches, PREG_SET_ORDER);
        $entries = [];
        foreach ($matches as $match) {
            if (Str::studly($match[1]) !== $match[2]
                || isset($entries[$match[1]]) || in_array($match[2], $entries, true)) {
                return null;
            }
            $entries[$match[1]] = $match[2];
        }

        return $entries;
    }

    /** @param array<string, string> $suites @param array<string, string> $scenarios @param array<string, string> $variants */
    private function hierarchyValid(
        string $content,
        string $componentKey,
        array &$suites,
        array &$scenarios,
        array &$variants,
    ): bool {
        preg_match_all("/SuiteDescriptor\\('([^']+)', '([^']+)'\\)/", $content, $suiteMatches, PREG_SET_ORDER);
        foreach ($suiteMatches as $match) {
            if ($match[2] !== $componentKey || isset($suites[$match[1]])) {
                return false;
            }
            $suites[$match[1]] = $componentKey;
        }
        preg_match_all("/ScenarioDescriptor\\('([^']+)', '([^']+)', '([^']+)'/", $content, $scenarioMatches, PREG_SET_ORDER);
        foreach ($scenarioMatches as $match) {
            $owner = $match[2]."\0".$match[3];
            $classCollision = false;
            foreach (array_keys($scenarios) as $scenarioKey) {
                if (Str::studly($scenarioKey) === Str::studly($match[1])) {
                    $classCollision = true;
                    break;
                }
            }
            if ($match[2] !== $componentKey || ($suites[$match[3]] ?? null) !== $componentKey
                || isset($scenarios[$match[1]]) || $classCollision) {
                return false;
            }
            $scenarios[$match[1]] = $owner;
        }
        preg_match_all("/\\\$scenarioKey === '([^']+)'\\) \\{ yield new VariantDescriptor\\('([^']+)'\\); \\}/", $content, $variantMatches, PREG_SET_ORDER);
        foreach ($variantMatches as $match) {
            $identity = $match[1]."\0".$match[2];
            if (! isset($scenarios[$match[1]]) || isset($variants[$identity])) {
                return false;
            }
            $variants[$identity] = $componentKey;
        }

        return true;
    }

    /**
     * @param  array<string, string>  $components
     * @param  array<string, string>  $suites
     * @param  array<string, string>  $scenarios
     * @param  array<string, string>  $variants
     */
    private function sourceMappingsValid(
        string $content,
        array $components,
        array $suites,
        array $scenarios,
        array $variants,
    ): bool {
        $count = substr_count($content, 'new SourceCaseMapping(');
        $key = "(?:null|'[a-z0-9][a-z0-9._-]{0,63}')";
        $reason = "(?:null|'(?:\\\\['\\\\]|[^'\\\\\\x00-\\x1F\\x7F])*')";
        $list = "\\[(?:'[a-z0-9][a-z0-9._-]{0,63}'(?:, '[a-z0-9][a-z0-9._-]{0,63}')*)?\\]";
        $pattern = '~^[ \\t]*new SourceCaseMapping\\('
            ."sourceCaseId: '(?<source>[A-Za-z0-9][A-Za-z0-9._:-]{0,127})', "
            .'disposition: CoverageDisposition::(?<disposition>AUTOMATED_FULL|AUTOMATED_PARTIAL|MERGED_EQUIVALENT|EXCLUDED_NO_RELIABLE_EXECUTOR|EXCLUDED_HUMAN_JUDGMENT), '
            ."componentKey: (?<component>{$key}), suiteKey: (?<suite>{$key}), "
            ."scenarioKey: (?<scenario>{$key}), variantKey: (?<variant>{$key}), "
            ."replacementComponentKey: (?<replacement_component>{$key}), replacementSuiteKey: (?<replacement_suite>{$key}), "
            ."replacementScenarioKey: (?<replacement_scenario>{$key}), replacementVariantKey: (?<replacement_variant>{$key}), "
            ."reason: (?<reason>{$reason}), coveredAssertions: (?<covered>{$list}), "
            ."uncoveredAssertions: (?<uncovered>{$list})\\),[ \\t]*\\r?$~m";
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        if ($count !== count($matches)) {
            return false;
        }

        $sources = [];
        $identities = [];
        $replacements = [];
        foreach ($matches as $match) {
            try {
                $mapping = new SourceCaseMapping(
                    $match['source'],
                    CoverageDisposition::from(strtolower($match['disposition'])),
                    $this->nullableLiteral($match['component']),
                    $this->nullableLiteral($match['suite']),
                    $this->nullableLiteral($match['scenario']),
                    $this->nullableLiteral($match['variant']),
                    $this->nullableLiteral($match['replacement_component']),
                    $this->nullableLiteral($match['replacement_suite']),
                    $this->nullableLiteral($match['replacement_scenario']),
                    $this->nullableLiteral($match['replacement_variant']),
                    $this->nullableLiteral($match['reason']),
                    $this->literalList($match['covered']),
                    $this->literalList($match['uncovered']),
                );
            } catch (Throwable) {
                return false;
            }
            $identity = $mapping->executableIdentity();
            $replacement = $mapping->replacementIdentity();
            $identityKey = $identity === null ? null : implode("\0", $identity);
            $replacementKey = $replacement === null ? null : implode("\0", $replacement);
            if (isset($sources[$mapping->sourceCaseId])
                || ($identityKey !== null && isset($identities[$identityKey]))
                || ($replacementKey !== null && isset($replacements[$replacementKey]))
                || ! $this->mappingIdentityValid($mapping, $components, $suites, $scenarios, $variants)) {
                return false;
            }
            $sources[$mapping->sourceCaseId] = true;
            if ($identityKey !== null) {
                $identities[$identityKey] = true;
            }
            if ($replacementKey !== null) {
                $replacements[$replacementKey] = true;
            }
        }

        return true;
    }

    /**
     * @param  array<string, string>  $components
     * @param  array<string, string>  $suites
     * @param  array<string, string>  $scenarios
     * @param  array<string, string>  $variants
     */
    private function mappingIdentityValid(
        SourceCaseMapping $mapping,
        array $components,
        array $suites,
        array $scenarios,
        array $variants,
    ): bool {
        $identity = $mapping->executableIdentity() ?? $mapping->replacementIdentity();
        if ($identity === null) {
            return true;
        }
        [$component, $suite, $scenario, $variant] = $identity;

        return isset($components[$component])
            && ($suites[$suite] ?? null) === $component
            && ($scenarios[$scenario] ?? null) === $component."\0".$suite
            && isset($variants[$scenario."\0".$variant]);
    }

    private function nullableLiteral(string $literal): ?string
    {
        if ($literal === 'null') {
            return null;
        }
        $value = '';
        $inner = substr($literal, 1, -1);
        for ($index = 0, $length = strlen($inner); $index < $length; $index++) {
            if ($inner[$index] !== '\\') {
                $value .= $inner[$index];

                continue;
            }
            if (++$index >= $length || ! in_array($inner[$index], ["'", '\\'], true)) {
                throw TargetModuleException::because('acceptance_source_mapping_invalid');
            }
            $value .= $inner[$index];
        }

        return $value;
    }

    /** @return list<string> */
    private function literalList(string $literal): array
    {
        preg_match_all("/'([a-z0-9][a-z0-9._-]{0,63})'/", $literal, $matches);

        return $matches[1] ?? [];
    }

    private function contained(string $root, string $path): bool
    {
        $rootComparison = PHP_OS_FAMILY === 'Windows' ? strtolower($root) : $root;
        $pathComparison = PHP_OS_FAMILY === 'Windows' ? strtolower($path) : $path;

        return dirname($pathComparison) === $rootComparison;
    }

    private function normalize(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $segments = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' && $segments === []) {
                $segments[] = '';
            } elseif ($segment === '' || $segment === '.') {
                continue;
            } elseif ($segment === '..') {
                array_pop($segments);
            } else {
                $segments[] = $segment;
            }
        }

        return rtrim(implode('/', $segments), '/');
    }

    private function join(string $root, string $relative): string
    {
        return rtrim($root, '/\\').DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    private function relative(string $root, string $path): string
    {
        return str_replace('\\', '/', substr($path, strlen(rtrim($root, '/\\')) + 1));
    }
}
