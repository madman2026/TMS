<?php

namespace App\Acceptance\Operations\Data;

use InvalidArgumentException;

final readonly class FileChange
{
    public function __construct(public string $path, public string $action)
    {
        if ($path === '' || str_contains($path, '\\') || str_starts_with($path, '/')
            || preg_match('/(^|\/)\.\.?($|\/)/D', $path)
            || ! in_array($action, ['create', 'update_managed_region', 'unchanged'], true)) {
            throw new InvalidArgumentException('file_change_invalid');
        }
    }
}
