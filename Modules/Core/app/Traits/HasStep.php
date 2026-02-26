<?php

namespace Modules\Core\Traits;

use Closure;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Contracts\StepResult;


trait HasStep
{
    public function step(string $name, Closure $callback, ?string $description = null, bool $critical = true, TestContext $context): StepResult
    {
        $stepResult = $this->executeStep($name, $callback, $critical);
        $context->browser->close();
        $context->test->steps()->create([
            'name' => $stepResult->name,
            'duration' => $stepResult->duration,
            'description' => $description,
            'data' => $stepResult->results,
            'status' => $stepResult->passed ? 'finished' : 'failed'
        ]);

        if (!$stepResult->passed && $stepResult->critical) {
            throw new \RuntimeException("Critical step '{$stepResult->name}' failed: {$stepResult->error}");
        }

        return $stepResult;
    }
}
