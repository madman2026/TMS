<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Data;

use Modules\DK\Acceptance\Components\NotificationDelivery\Exceptions\NotificationDeliverySimulationException;

final readonly class NotificationSubmission
{
    public function __construct(
        public string $notificationKey,
        public string $providerReference,
        public string $correlationId,
        public int $acceptedAtMilliseconds,
        public int $version = 1,
    ) {
        if (
            ! self::isSafeKey($notificationKey)
            || ! self::isSafeReference($providerReference)
            || ! self::isUuid($correlationId)
            || $acceptedAtMilliseconds < 0
            || $version !== 1
        ) {
            throw NotificationDeliverySimulationException::because('notification_request_invalid');
        }
    }

    private static function isSafeKey(string $value): bool
    {
        return strlen($value) <= 64
            && preg_match('/\A[a-z0-9][a-z0-9._-]*\z/D', $value) === 1;
    }

    private static function isSafeReference(string $value): bool
    {
        return strlen($value) <= 128
            && preg_match('/\A[a-z0-9][a-z0-9._-]*\z/D', $value) === 1;
    }

    private static function isUuid(string $value): bool
    {
        return preg_match(
            '/\A[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/D',
            $value,
        ) === 1;
    }
}
