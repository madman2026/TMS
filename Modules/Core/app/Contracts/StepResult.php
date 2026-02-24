<?php

namespace Modules\Core\Contracts;


class StepResult
{
    public function __construct(
        public string $name,
        public bool $passed,
        public ?string $error = null,
        public float $duration = 0,
        public array $results = [],
        public bool $critical = true,
    ){}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'passed' => $this->passed,
            'error' => $this->error,
            'duration' => $this->duration,
            'results' => $this->results,
            'critical' => $this->critical,
        ];
    }
}
