<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ConfigurationSurfaceTest extends TestCase
{
    public function test_every_active_first_party_environment_reference_is_declared_once_in_env_example(): void
    {
        $references = [];

        foreach ($this->configurationFiles() as $file) {
            $references = [...$references, ...$this->activeEnvironmentReferences($file)];
        }

        $references = array_values(array_unique($references));
        sort($references);

        $declarationCounts = $this->environmentExampleDeclarationCounts();
        $missing = array_values(array_diff($references, array_keys($declarationCounts)));
        $duplicates = array_keys(array_filter(
            $declarationCounts,
            static fn (int $count): bool => $count !== 1,
        ));

        $this->assertContains('APP_KEY', $references);
        $this->assertContains('SESSION_COOKIE', $references);
        $this->assertContains('SCRIBE_AUTH_KEY', $references);
        $this->assertSame([], $missing, 'Missing .env.example declarations: '.implode(', ', $missing));
        $this->assertSame([], $duplicates, 'Duplicate .env.example declarations: '.implode(', ', $duplicates));
    }

    public function test_first_party_source_reads_environment_only_from_configuration_files(): void
    {
        $violations = [];

        foreach ($this->firstPartyPhpFiles() as $file) {
            if ($this->isConfigurationFile($file)) {
                continue;
            }

            foreach ($this->activeEnvironmentReferences($file) as $reference) {
                $violations[] = $this->relativePath($file).':'.$reference;
            }
        }

        sort($violations);

        $this->assertSame([], $violations, 'Environment reads outside config: '.implode(', ', $violations));
    }

    public function test_required_configuration_namespaces_and_keys_resolve_after_application_boot(): void
    {
        $this->assertIsArray(config('acceptance.queue'));
        $this->assertSame('Core', config('core.name'));
        $this->assertIsString(config('core.acceptance.browser'));
        $this->assertIsBool(config('core.acceptance.headless'));
        $this->assertIsInt(config('core.acceptance.timeout_ms'));
        $this->assertIsInt(config('core.acceptance.slow_mo_ms'));
        $this->assertSame('DK', config('dk.name'));
        $this->assertSame('Modules', config('modules.namespace'));
        $this->assertIsArray(config('view.paths'));
        $this->assertNotSame([], config('view.paths'));
        $this->assertFileDoesNotExist(config_path('modules.php'));
        $this->assertFileDoesNotExist(config_path('view.php'));
    }

    public function test_sensitive_environment_examples_do_not_contain_real_values(): void
    {
        $sensitiveKeys = [
            'APP_KEY',
            'APP_PREVIOUS_KEYS',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'DB_PASSWORD',
            'LOG_SLACK_WEBHOOK_URL',
            'MAIL_PASSWORD',
            'MAIL_USERNAME',
            'MEMCACHED_PASSWORD',
            'PAPERTRAIL_URL',
            'POSTMARK_API_KEY',
            'REDIS_PASSWORD',
            'RESEND_API_KEY',
            'SCRIBE_AUTH_KEY',
            'SLACK_BOT_USER_OAUTH_TOKEN',
        ];
        $declarations = $this->environmentExampleDeclarations();
        $unsafeKeys = [];

        foreach ($sensitiveKeys as $key) {
            $value = strtolower(trim($declarations[$key] ?? '', " \t\n\r\0\x0B\"'"));

            if (! in_array($value, ['', 'null', '(null)'], true)) {
                $unsafeKeys[] = $key;
            }
        }

        $this->assertSame([], $unsafeKeys, 'Sensitive .env.example values must remain empty or null: '.implode(', ', $unsafeKeys));
    }

    /**
     * @return list<string>
     */
    private function configurationFiles(): array
    {
        return array_values(array_filter(
            $this->firstPartyPhpFiles(),
            fn (string $file): bool => $this->isConfigurationFile($file),
        ));
    }

    /**
     * @return list<string>
     */
    private function firstPartyPhpFiles(): array
    {
        $files = [];

        foreach (['app', 'bootstrap', 'config', 'database', 'Modules', 'resources', 'routes', 'tests'] as $directory) {
            $path = base_path($directory);

            if (! File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                $normalizedPath = str_replace('\\', '/', $file->getPathname());

                if ($file->getExtension() === 'php' && ! str_contains($normalizedPath, '/bootstrap/cache/')) {
                    $files[] = $file->getPathname();
                }
            }
        }

        sort($files);

        return $files;
    }

    private function isConfigurationFile(string $file): bool
    {
        $relativePath = $this->relativePath($file);

        return str_starts_with($relativePath, 'config/')
            || preg_match('#^Modules/[^/]+/config/#', $relativePath) === 1;
    }

    /**
     * @return list<string>
     */
    private function activeEnvironmentReferences(string $file): array
    {
        $source = File::get($file);

        if (preg_match('/\benv\s*\(/', $source) !== 1) {
            return [];
        }

        $tokens = token_get_all($source);
        $references = [];

        foreach ($tokens as $index => $token) {
            if (! is_array($token) || $token[0] !== T_STRING || strtolower($token[1]) !== 'env') {
                continue;
            }

            $openParenthesis = $this->nextMeaningfulTokenIndex($tokens, $index + 1);

            if ($openParenthesis === null || $tokens[$openParenthesis] !== '(') {
                continue;
            }

            $argument = $this->nextMeaningfulTokenIndex($tokens, $openParenthesis + 1);

            if ($argument === null || ! is_array($tokens[$argument]) || $tokens[$argument][0] !== T_CONSTANT_ENCAPSED_STRING) {
                continue;
            }

            $references[] = trim($tokens[$argument][1], "'\"");
        }

        return $references;
    }

    /**
     * @param  array<int, array{int, string, int}|string>  $tokens
     */
    private function nextMeaningfulTokenIndex(array $tokens, int $start): ?int
    {
        for ($index = $start, $count = count($tokens); $index < $count; $index++) {
            $token = $tokens[$index];

            if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            return $index;
        }

        return null;
    }

    /**
     * @return array<string, int>
     */
    private function environmentExampleDeclarationCounts(): array
    {
        $counts = [];

        foreach (preg_split('/\R/', File::get(base_path('.env.example'))) ?: [] as $line) {
            if (preg_match('/^\s*#?\s*([A-Z][A-Z0-9_]*)=/', $line, $matches) !== 1) {
                continue;
            }

            $counts[$matches[1]] = ($counts[$matches[1]] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * @return array<string, string>
     */
    private function environmentExampleDeclarations(): array
    {
        $declarations = [];

        foreach (preg_split('/\R/', File::get(base_path('.env.example'))) ?: [] as $line) {
            if (preg_match('/^\s*#?\s*([A-Z][A-Z0-9_]*)=(.*)$/', $line, $matches) === 1) {
                $declarations[$matches[1]] = $matches[2];
            }
        }

        return $declarations;
    }

    private function relativePath(string $file): string
    {
        return str_replace('\\', '/', substr($file, strlen(base_path()) + 1));
    }
}
