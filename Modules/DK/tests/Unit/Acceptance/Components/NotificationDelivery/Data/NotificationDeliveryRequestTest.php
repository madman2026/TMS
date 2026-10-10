<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit\Acceptance\Components\NotificationDelivery\Data;

use Modules\DK\Acceptance\Components\NotificationDelivery\Data\NotificationDeliveryRequest;
use Modules\DK\Acceptance\Components\NotificationDelivery\Exceptions\NotificationDeliverySimulationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class NotificationDeliveryRequestTest extends TestCase
{
    private const CORRELATION_ID = 'dcb1cf9d-207c-4a44-963b-000000000101';

    public function test_it_preserves_only_the_approved_safe_fields(): void
    {
        $request = new NotificationDeliveryRequest(
            notificationKey: 'notification-a',
            recipientReference: 'recipient-a',
            templateKey: 'template-a',
            correlationId: self::CORRELATION_ID,
        );

        $this->assertSame('notification-a', $request->notificationKey);
        $this->assertSame('recipient-a', $request->recipientReference);
        $this->assertSame('template-a', $request->templateKey);
        $this->assertSame(self::CORRELATION_ID, $request->correlationId);
        $this->assertSame(1, $request->version);

        $properties = array_map(
            static fn ($property): string => $property->getName(),
            (new ReflectionClass($request))->getProperties(),
        );
        sort($properties);

        $this->assertSame([
            'correlationId',
            'notificationKey',
            'recipientReference',
            'templateKey',
            'version',
        ], $properties);
        $this->assertObjectNotHasProperty('address', $request);
        $this->assertObjectNotHasProperty('body', $request);
        $this->assertObjectNotHasProperty('payload', $request);
        $this->assertObjectNotHasProperty('token', $request);
    }

    #[DataProvider('invalidRequestProvider')]
    public function test_it_rejects_unsafe_request_values_without_exposing_them(array $overrides, string $sentinel): void
    {
        try {
            new NotificationDeliveryRequest(...array_merge([
                'notificationKey' => 'notification-a',
                'recipientReference' => 'recipient-a',
                'templateKey' => 'template-a',
                'correlationId' => self::CORRELATION_ID,
                'version' => 1,
            ], $overrides));

            $this->fail('Expected an invalid notification request to be rejected.');
        } catch (NotificationDeliverySimulationException $exception) {
            $this->assertSame('notification_request_invalid', $exception->errorCode);
            $this->assertSame('notification_request_invalid', $exception->getMessage());
            $this->assertFalse($exception->retryable);
            $this->assertTrue($exception->permanent);
            $this->assertFalse($exception->adminActionRequired);
            $this->assertStringNotContainsString($sentinel, $exception->getMessage());
        }
    }

    public static function invalidRequestProvider(): iterable
    {
        yield 'empty notification key' => [['notificationKey' => ''], 'empty-sentinel'];
        yield 'uppercase key' => [['notificationKey' => 'UnsafeKey'], 'UnsafeKey'];
        yield 'free form recipient' => [['recipientReference' => 'private recipient'], 'private recipient'];
        yield 'phone-like recipient' => [['recipientReference' => '0000000000'], '0000000000'];
        yield 'control character' => [['templateKey' => "template\nsecret"], 'secret'];
        yield 'overlong key' => [['templateKey' => str_repeat('a', 101)], str_repeat('a', 101)];
        yield 'invalid correlation' => [['correlationId' => 'correlation-secret'], 'correlation-secret'];
        yield 'wrong version' => [['version' => 2], 'version-sentinel'];
    }
}
