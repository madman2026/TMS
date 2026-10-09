<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\Data\ExecutorCapability;
use Modules\Core\Data\ExecutorRequest;
use Modules\Core\Data\ExecutorResult;

interface AcceptanceExecutor
{
    public function key(): string;

    /** @return list<ExecutorCapability> */
    public function capabilities(): array;

    public function execute(ExecutorRequest $request): ExecutorResult;
}
