<?php

namespace App\Acceptance\Modules;

use App\Data\ScenarioDescriptor;

/** Validated names used to derive every generated path and PHP symbol. */
final readonly class TargetModuleDefinition
{
    public string $moduleAlias;

    public string $moduleNamespace;

    public string $namespace;

    public function __construct(
        public string $moduleName,
        public string $appKey,
        string $moduleNamespace = 'Modules',
    ) {
        if (! preg_match('/^[A-Z][A-Za-z0-9]{0,63}$/D', $moduleName)) {
            throw TargetModuleException::because('target_module_name_invalid');
        }
        try {
            ScenarioDescriptor::assertKey($appKey, 'acceptance_hierarchy_key_invalid');
        } catch (\Throwable) {
            throw TargetModuleException::because('acceptance_hierarchy_key_invalid');
        }
        if (! preg_match('/^[A-Z][A-Za-z0-9]*(?:\\\\[A-Z][A-Za-z0-9]*)*$/D', $moduleNamespace)) {
            throw TargetModuleException::because('target_module_path_invalid');
        }

        $this->moduleAlias = strtolower($moduleName);
        $this->moduleNamespace = $moduleNamespace;
        $this->namespace = $moduleNamespace.'\\'.$moduleName;
    }
}
