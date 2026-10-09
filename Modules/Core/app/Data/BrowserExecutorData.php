<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use Modules\Core\Exceptions\ExecutorException;

final readonly class BrowserExecutorData
{
    /** @var list<BrowserStepData> */
    public array $steps;

    /** @param list<BrowserStepData> $steps */
    public function __construct(
        public string $scenarioName,
        public bool $passed,
        public int $durationMs,
        array $steps,
    ) {
        if ($durationMs < 0 || $durationMs > 86_400_000
            || $scenarioName === '' || strlen($scenarioName) > 128
            || preg_match('//u', $scenarioName) !== 1
            || preg_match('/[\p{C}]/u', $scenarioName) === 1
            || ! array_is_list($steps)) {
            throw new ExecutorException('unsafe_executor_result');
        }

        $containsFailedStep = false;
        $copy = [];
        foreach ($steps as $index => $step) {
            if (! $step instanceof BrowserStepData || $step->position !== $index + 1) {
                throw new ExecutorException('unsafe_executor_result');
            }

            $containsFailedStep = $containsFailedStep || ! $step->passed;
            $copy[] = $step;
        }

        if ($passed === $containsFailedStep) {
            throw new ExecutorException('unsafe_executor_result');
        }

        $this->steps = $copy;
    }
}
