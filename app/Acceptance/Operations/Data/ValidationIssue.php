<?php

namespace App\Acceptance\Operations\Data;

use InvalidArgumentException;

final readonly class ValidationIssue
{
    public const CODES = [
        'target_module_required_path_missing', 'target_module_manifest_invalid',
        'target_module_composer_invalid', 'target_module_provider_invalid',
        'target_module_registration_missing', 'target_module_acceptance_app_invalid',
        'target_module_managed_region_invalid', 'target_module_surface_forbidden',
        'acceptance_hierarchy_invalid', 'acceptance_source_mapping_invalid',
    ];

    public function __construct(public string $code, public ?string $path = null)
    {
        if (! in_array($code, self::CODES, true)
            || ($path !== null && ($path === '' || str_contains($path, '\\') || str_starts_with($path, '/')
                || preg_match('/(^|\/)\.\.?($|\/)/D', $path)))) {
            throw new InvalidArgumentException('validation_issue_invalid');
        }
    }
}
