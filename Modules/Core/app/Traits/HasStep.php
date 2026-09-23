<?php

namespace Modules\Core\Traits;

use Closure;
use Modules\Core\Contracts\StepResult;

trait HasStep
{
    public function step(
        string $name,
        Closure $callback,
        ?string $description = null,
        bool $critical = true,
    ): StepResult {
        return $this->executeStep($name, $callback, $critical, $description);
    }
}
