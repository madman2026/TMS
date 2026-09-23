<?php

namespace App\Contracts;

use Modules\Core\Contracts\StepResult;
use Throwable;

abstract class BaseAction
{
    protected function executeStep(
        string $name,
        callable $callback,
        bool $critical = true,
        ?string $description = null,
    ): StepResult {
        $start = microtime(true);

        try {
            $result = $callback();
            $duration = microtime(true) - $start;

            return new StepResult(
                name: $name,
                passed: true,
                duration: $duration,
                results: is_array($result) ? $result : [$result],
                critical: $critical,
                description: $description,
            );
        } catch (Throwable $e) {
            $duration = microtime(true) - $start;

            return new StepResult(
                name: $name,
                passed: false,
                error: 'Step failed.',
                errorCode: 'acceptance_step_failed',
                duration: $duration,
                results: [],
                critical: $critical,
                description: $description,
                exceptionClass: $e::class,
            );
        }
    }
}
