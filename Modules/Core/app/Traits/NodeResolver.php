<?php
namespace Modules\Core\Traits;

use Illuminate\Support\Facades\Process;
use RuntimeException;

trait NodeResolver
{
    /**
     * Resolve the path to Node.js executable and set PLAYWRIGHT_NODE_PATH
     *
     * @return string
     * @throws RuntimeException
     */
    public function resolveNode(): string
    {
        // $process = Process::run('where node');
        // if (! $process->successful()) {
        //     throw new RuntimeException('Node.js not found in PATH.');
        // }

        // $paths = array_filter(explode(PHP_EOL, $process->output()), fn($line) => trim($line) !== '');
        // if (empty($paths)) {
        //     throw new RuntimeException('Node.js not found in PATH.');
        // }

        $nodePath = trim($paths[0]);

        putenv("PLAYWRIGHT_NODE_PATH=$nodePath");
        $_ENV['PLAYWRIGHT_NODE_PATH'] = $nodePath;
        $_SERVER['PLAYWRIGHT_NODE_PATH'] = $nodePath;

        return $nodePath;
    }
}
