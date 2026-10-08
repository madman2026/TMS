<?php

namespace Modules\Core\Data;

use Modules\Core\Contracts\StepResult;

final readonly class RunResult
{
    /**
     * @param  array<int, StepResult>  $steps
     */
    public function __construct(
        public AcceptanceExecutionIdentity $identity,
        public string $scenarioName,
        public bool $passed,
        public float $duration,
        public array $steps,
        public ?string $errorCode = null,
    ) {}
}
