<?php

namespace App\Acceptance\Modules\Contracts;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Modules\TargetModuleDefinition;
use App\Acceptance\Operations\Data\ModuleChangeData;

interface TargetModuleCreator
{
    public function create(TargetModuleDefinition $definition, bool $dryRun = true): ModuleChangeData;

    public function createComponent(string $moduleName, string $componentKey, bool $dryRun = true): ModuleChangeData;

    /** @param list<SourceCaseMapping> $mappings */
    public function importScenarios(string $moduleName, array $mappings, bool $dryRun = true): ModuleChangeData;
}
