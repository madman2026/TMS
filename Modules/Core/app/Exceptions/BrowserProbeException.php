<?php

declare(strict_types=1);

namespace Modules\Core\Exceptions;

use RuntimeException;

/** Safe boundary failure: no raw argument, library message, or chained cause. */
final class BrowserProbeException extends RuntimeException
{
    public const UNAVAILABLE_CODES = [
        'browser_probe_invalid_argument',
        'browser_probe_page_closed',
        'browser_probe_locator_missing',
        'browser_probe_locator_ambiguous',
        'browser_probe_geometry_unavailable',
        'browser_probe_invalid_payload',
        'browser_probe_operation_failed',
    ];

    public const UNSUPPORTED_CODES = [
        'browser_probe_capability_unsupported',
        'browser_probe_touch_unsupported',
        'browser_probe_contrast_unsupported',
    ];

    public readonly string $errorCode;

    public readonly bool $retryable;

    public function __construct(string $errorCode)
    {
        $this->errorCode = in_array($errorCode, [...self::UNAVAILABLE_CODES, ...self::UNSUPPORTED_CODES], true)
            ? $errorCode : 'browser_probe_invalid_payload';
        $this->retryable = false;

        parent::__construct('Browser probe could not provide the requested observation.');
    }
}
