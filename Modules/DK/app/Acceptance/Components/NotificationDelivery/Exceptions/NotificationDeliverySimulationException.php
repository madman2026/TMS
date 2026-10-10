<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Exceptions;

use RuntimeException;

final class NotificationDeliverySimulationException extends RuntimeException
{
    private const ERROR_CODES = [
        'notification_request_invalid',
        'notification_submission_conflict',
        'notification_not_found',
        'notification_callback_conflict',
        'notification_time_invalid',
    ];

    public readonly bool $retryable;

    public readonly bool $permanent;

    public readonly bool $adminActionRequired;

    private function __construct(public readonly string $errorCode)
    {
        parent::__construct($errorCode);

        $this->retryable = false;
        $this->permanent = true;
        $this->adminActionRequired = false;
    }

    public static function because(string $errorCode): self
    {
        if (! in_array($errorCode, self::ERROR_CODES, true)) {
            $errorCode = 'notification_request_invalid';
        }

        return new self($errorCode);
    }
}
