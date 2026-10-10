<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Data;

use Modules\DK\Acceptance\Components\NotificationDelivery\Enums\NotificationDeliveryEventType;
use Modules\DK\Acceptance\Components\NotificationDelivery\Exceptions\NotificationDeliverySimulationException;

final readonly class NotificationDeliveryEvent
{
    public function __construct(
        public string $notificationKey,
        public string $providerReference,
        public ?string $callbackKey,
        public NotificationDeliveryEventType $type,
        public string $correlationId,
        public int $occurredAtMilliseconds,
        public int $version = 1,
    ) {
        $callbackMatchesType = match ($type) {
            NotificationDeliveryEventType::ProviderSubmitted => $callbackKey === null,
            NotificationDeliveryEventType::CallbackDelivered,
            NotificationDeliveryEventType::CallbackFailed => $callbackKey !== null && self::isSafeKey($callbackKey),
        };

        if (
            ! self::isSafeKey($notificationKey)
            || ! self::isSafeReference($providerReference)
            || ! $callbackMatchesType
            || ! self::isUuid($correlationId)
            || $occurredAtMilliseconds < 0
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
