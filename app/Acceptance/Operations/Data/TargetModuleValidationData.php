<?php

namespace App\Acceptance\Operations\Data;

use InvalidArgumentException;

final readonly class TargetModuleValidationData
{
    /** @var list<ValidationIssue> */
    public array $issues;

    /** @var list<string> */
    public array $checkedPaths;

    /**
     * @param  list<ValidationIssue>  $issues
     * @param  list<string>  $checkedPaths
     */
    public function __construct(
        public string $moduleName,
        public ?string $appKey,
        public bool $valid,
        array $issues,
        array $checkedPaths,
        public int $version = 1,
    ) {
        if ($version !== 1 || $moduleName === '' || $valid !== ($issues === [])) {
            throw new InvalidArgumentException('target_module_validation_data_invalid');
        }
        foreach ($issues as $issue) {
            if (! $issue instanceof ValidationIssue) {
                throw new InvalidArgumentException('target_module_validation_data_invalid');
            }
        }
        sort($checkedPaths, SORT_STRING);
        usort($issues, fn (ValidationIssue $a, ValidationIssue $b): int => [$a->code, $a->path] <=> [$b->code, $b->path]);
        $this->issues = array_values($issues);
        $this->checkedPaths = array_values(array_unique($checkedPaths));
    }
}
