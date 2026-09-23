<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CoreIsolationTest extends TestCase
{
    #[DataProvider('forbiddenCoreDependencyProvider')]
    public function test_core_has_no_project_or_target_specific_dependency(string $forbidden): void
    {
        $root = dirname(__DIR__, 2).'/Modules/Core/app';

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            $this->assertStringNotContainsString(
                $forbidden,
                $contents,
                "Forbidden Core dependency [{$forbidden}] found in {$file->getPathname()}.",
            );
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function forbiddenCoreDependencyProvider(): array
    {
        return [
            'root models' => ['App\\Models'],
            'http request' => ['Illuminate\\Http\\Request'],
            'dieselkhodro' => ['dieselkhodro'],
            'martfury' => ['martfury'],
            'DKAPI' => ['DKAPI'],
            'Auth module' => ['Modules\\Auth'],
            'Ecommerce module' => ['Modules\\Ecommerce'],
        ];
    }
}
