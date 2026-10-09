<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\Data\ExecutorCapability;
use Modules\Core\Data\ExecutorRequest;
use Modules\Core\Data\ExecutorResult;

interface ExecutorRegistry
{
    public function register(AcceptanceExecutor $executor): void;

    public function for(ExecutorCapability $capability): ?AcceptanceExecutor;

    public function execute(ExecutorRequest $request): ExecutorResult;
}
