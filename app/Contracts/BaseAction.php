<?php

namespace App\Contracts;

use Throwable;
use Illuminate\Support\Facades\Log;

abstract class BaseAction
{
    protected function executeStep(string $name, callable $callback, bool $critical = true): StepResult
    {
        $start = microtime(true);

        try {
            $result = $callback();
            $duration = microtime(true) - $start;
            return new StepResult(
                name: $name,
                passed: true,
                duration: $duration,
                results: is_array($result) ? $result : [$result],
                critical: $critical
            );
        } catch (Throwable $e) {
            $duration = microtime(true) - $start;

            Log::error("Step '{$name}' failed", [
                "exception" => $e,
                "trace" => $e->getTraceAsString(),
            ]);

            return new StepResult(
                name: $name,
                passed: false,
                error: $e->getMessage(),
                duration: $duration,
                results: [],
                critical: $critical
            );
        }
    }
}
