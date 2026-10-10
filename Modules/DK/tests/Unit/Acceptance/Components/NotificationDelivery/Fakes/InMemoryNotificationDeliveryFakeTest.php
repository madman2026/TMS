<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit\Acceptance\Components\NotificationDelivery\Fakes;

use Modules\DK\Acceptance\Components\NotificationDelivery\Data\NotificationDeliveryRequest;
use Modules\DK\Acceptance\Components\NotificationDelivery\Enums\NotificationCallbackOutcome;
use Modules\DK\Acceptance\Components\NotificationDelivery\Enums\NotificationDeliveryEventType;
use Modules\DK\Acceptance\Components\NotificationDelivery\Enums\NotificationWorkerState;
use Modules\DK\Acceptance\Components\NotificationDelivery\Exceptions\NotificationDeliverySimulationException;
use Modules\DK\Acceptance\Components\NotificationDelivery\Fakes\InMemoryNotificationDeliveryFake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class InMemoryNotificationDeliveryFakeTest extends TestCase
{
    private const CORRELATION_ID = 'dcb1cf9d-207c-4a44-963b-000000000201';

    public function test_it_submits_and_observes_a_notification_deterministically(): void
    {
        $fake = new InMemoryNotificationDeliveryFake;
        $request = $this->request();

        $submission = $fake->submit($request);

        $this->assertSame('notification-a', $submission->notificationKey);
        $this->assertSame('fake-notification-a', $submission->providerReference);
        $this->assertSame(self::CORRELATION_ID, $submission->correlationId);
        $this->assertSame(0, $submission->acceptedAtMilliseconds);
        $this->assertSame(1, $submission->version);
        $this->assertSame([
            'notificationKey',
            'providerReference',
            'correlationId',
            'acceptedAtMilliseconds',
            'version',
        ], array_keys(get_object_vars($submission)));
        $this->assertSame(NotificationWorkerState::Queued, $fake->workerState('notification-a'));

        $events = $fake->events('notification-a');
        $this->assertCount(1, $events);
        $this->assertSame(NotificationDeliveryEventType::ProviderSubmitted, $events[0]->type);
        $this->assertNull($events[0]->callbackKey);
        $this->assertSame(self::CORRELATION_ID, $events[0]->correlationId);
        $this->assertSame(0, $events[0]->occurredAtMilliseconds);
        $this->assertSame([
            'notificationKey',
            'providerReference',
            'callbackKey',
            'type',
            'correlationId',
            'occurredAtMilliseconds',
            'version',
        ], array_keys(get_object_vars($events[0])));

        unset($events[0]);
        $this->assertCount(1, $fake->events('notification-a'));
    }

    public function test_identical_submit_and_callback_replays_are_idempotent(): void
    {
        $fake = new InMemoryNotificationDeliveryFake;
        $request = $this->request();

        $submission = $fake->submit($request);
        $replayedSubmission = $fake->submit($this->request());
        $fake->advanceMilliseconds(25);
        $event = $fake->simulate('notification-a', 'callback-a', NotificationCallbackOutcome::Delivered);
        $replayedEvent = $fake->simulate('notification-a', 'callback-a', NotificationCallbackOutcome::Delivered);

        $this->assertSame($submission, $replayedSubmission);
        $this->assertSame($event, $replayedEvent);
        $this->assertSame(NotificationDeliveryEventType::CallbackDelivered, $event->type);
        $this->assertSame('callback-a', $event->callbackKey);
        $this->assertSame(25, $event->occurredAtMilliseconds);
        $this->assertSame(NotificationWorkerState::Processed, $fake->workerState('notification-a'));
        $this->assertCount(2, $fake->events('notification-a'));
    }

    public function test_conflicting_submit_preserves_original_state(): void
    {
        $fake = new InMemoryNotificationDeliveryFake;
        $original = $fake->submit($this->request());

        $this->assertSimulationCode(
            'notification_submission_conflict',
            fn () => $fake->submit($this->request(templateKey: 'template-b')),
        );

        $this->assertSame($original, $fake->submit($this->request()));
        $this->assertCount(1, $fake->events('notification-a'));
        $this->assertSame(NotificationWorkerState::Queued, $fake->workerState('notification-a'));
    }

    public function test_unknown_notification_operations_fail_without_creating_state(): void
    {
        $fake = new InMemoryNotificationDeliveryFake;

        foreach ([
            fn () => $fake->simulate('missing-notification', 'callback-a', NotificationCallbackOutcome::Failed),
            fn () => $fake->workerState('missing-notification'),
            fn () => $fake->events('missing-notification'),
        ] as $operation) {
            $this->assertSimulationCode('notification_not_found', $operation);
        }

        $this->assertSame(0, $fake->currentMilliseconds());
    }

    public function test_callback_conflicts_preserve_the_first_terminal_result(): void
    {
        $fake = new InMemoryNotificationDeliveryFake;
        $fake->submit($this->request());
        $original = $fake->simulate('notification-a', 'callback-a', NotificationCallbackOutcome::Delivered);

        $this->assertSimulationCode(
            'notification_callback_conflict',
            fn () => $fake->simulate('notification-a', 'callback-a', NotificationCallbackOutcome::Failed),
        );
        $this->assertSimulationCode(
            'notification_callback_conflict',
            fn () => $fake->simulate('notification-a', 'callback-b', NotificationCallbackOutcome::Failed),
        );

        $events = $fake->events('notification-a');
        $this->assertCount(2, $events);
        $this->assertSame($original, $events[1]);
        $this->assertSame(NotificationWorkerState::Processed, $fake->workerState('notification-a'));
    }

    public function test_failed_callback_produces_the_expected_terminal_event(): void
    {
        $fake = new InMemoryNotificationDeliveryFake;
        $fake->submit($this->request());

        $event = $fake->simulate('notification-a', 'callback-a', NotificationCallbackOutcome::Failed);

        $this->assertSame(NotificationDeliveryEventType::CallbackFailed, $event->type);
        $this->assertSame(NotificationWorkerState::Processed, $fake->workerState('notification-a'));
    }

    public function test_invalid_callback_key_preserves_queued_state(): void
    {
        $fake = new InMemoryNotificationDeliveryFake;
        $fake->submit($this->request());

        $this->assertSimulationCode(
            'notification_request_invalid',
            fn () => $fake->simulate('notification-a', 'unsafe callback', NotificationCallbackOutcome::Delivered),
        );

        $this->assertSame(NotificationWorkerState::Queued, $fake->workerState('notification-a'));
        $this->assertCount(1, $fake->events('notification-a'));
    }

    #[DataProvider('invalidTimeDeltaProvider')]
    public function test_invalid_time_advance_does_not_change_time(int $delta): void
    {
        $fake = new InMemoryNotificationDeliveryFake;

        $this->assertSimulationCode(
            'notification_time_invalid',
            fn () => $fake->advanceMilliseconds($delta),
        );

        $this->assertSame(0, $fake->currentMilliseconds());
    }

    public static function invalidTimeDeltaProvider(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
        yield 'above one day' => [86_400_001];
        yield 'integer maximum' => [PHP_INT_MAX];
    }

    public function test_time_overflow_is_rejected_without_mutation(): void
    {
        $fake = new InMemoryNotificationDeliveryFake;
        $time = new ReflectionProperty($fake, 'currentMilliseconds');
        $time->setValue($fake, PHP_INT_MAX - 1);

        $this->assertSimulationCode(
            'notification_time_invalid',
            fn () => $fake->advanceMilliseconds(2),
        );

        $this->assertSame(PHP_INT_MAX - 1, $fake->currentMilliseconds());
    }

    private function request(string $templateKey = 'template-a'): NotificationDeliveryRequest
    {
        return new NotificationDeliveryRequest(
            notificationKey: 'notification-a',
            recipientReference: 'recipient-a',
            templateKey: $templateKey,
            correlationId: self::CORRELATION_ID,
        );
    }

    private function assertSimulationCode(string $expectedCode, callable $operation): void
    {
        try {
            $operation();
            $this->fail('Expected the notification simulation operation to fail.');
        } catch (NotificationDeliverySimulationException $exception) {
            $this->assertSame($expectedCode, $exception->errorCode);
            $this->assertSame($expectedCode, $exception->getMessage());
            $this->assertFalse($exception->retryable);
            $this->assertTrue($exception->permanent);
            $this->assertFalse($exception->adminActionRequired);
        }
    }
}
