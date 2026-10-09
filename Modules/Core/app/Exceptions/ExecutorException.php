<?php

declare(strict_types=1);

namespace Modules\Core\Exceptions;

use RuntimeException;

final class ExecutorException extends RuntimeException
{
    public const ERROR_CODES = [
        'executor_registry_invalid',
        'executor_request_invalid',
        'unsafe_executor_result',
    ];

    public readonly string $errorCode;

    public function __construct(string $errorCode)
    {
        $this->errorCode = in_array($errorCode, self::ERROR_CODES, true)
            ? $errorCode
            : 'unsafe_executor_result';

        parent::__construct('The executor boundary rejected invalid or unsafe data.');
    }
}
