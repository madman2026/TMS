<?php

namespace Modules\Core\Contracts;

class StepResult
{
    public function __construct(
        public string $name,
        public bool $passed,
        public ?string $error = null,
        public ?string $errorCode = null,
        public float $duration = 0,
        public array $results = [],
        public bool $critical = true,
        public ?string $description = null,
        public ?string $exceptionClass = null,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'passed' => $this->passed,
            'error' => $this->error,
            'error_code' => $this->errorCode,
            'duration' => $this->duration,
            'critical' => $this->critical,
            'description' => $this->description,
        ];
    }
}
