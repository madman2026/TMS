<?php

namespace App\Exceptions;

use RuntimeException;

/** Fixed codes/messages deliberately discard provider data and exception chains. */
final class AcceptanceCatalogException extends RuntimeException
{
    private const MESSAGES = [
        'acceptance_selector_invalid' => 'The catalog selector is invalid.',
        'acceptance_app_not_found' => 'The selected App is not registered.',
        'acceptance_selector_not_found' => 'A selector is absent from the selected catalog scope.',
        'acceptance_catalog_invalid' => 'The code-owned catalog definition is invalid.',
        'acceptance_catalog_duplicate' => 'The visited catalog contains a duplicate identity.',
        'acceptance_catalog_limit_exceeded' => 'The catalog selection exceeds its traversal or row budget.',
        'acceptance_catalog_changed' => 'The code-owned catalog changed during inspection.',
        'acceptance_catalog_failed' => 'The code-owned catalog provider failed.',
        'acceptance_variant_not_executable' => 'The selected variant is not automated.',
    ];

    private function __construct(public readonly string $errorCode)
    {
        parent::__construct(self::MESSAGES[$errorCode]);
    }

    public static function because(string $errorCode): self
    {
        return new self(array_key_exists($errorCode, self::MESSAGES)
            ? $errorCode
            : 'acceptance_catalog_failed');
    }

    public function rejected(): bool
    {
        return in_array($this->errorCode, [
            'acceptance_selector_invalid',
            'acceptance_app_not_found',
            'acceptance_selector_not_found',
            'acceptance_catalog_limit_exceeded',
            'acceptance_variant_not_executable',
        ], true);
    }
}
