<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Fakes;

use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationAuditOracle;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationCallbackSimulator;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationProvider;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationTimeOracle;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationWorkerOracle;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\NotificationDeliveryEvent;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\NotificationDeliveryRequest;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\NotificationSubmission;
use Modules\DK\Acceptance\Components\NotificationDelivery\Enums\NotificationCallbackOutcome;
use Modules\DK\Acceptance\Components\NotificationDelivery\Enums\NotificationDeliveryEventType;
use Modules\DK\Acceptance\Components\NotificationDelivery\Enums\NotificationWorkerState;
use Modules\DK\Acceptance\Components\NotificationDelivery\Exceptions\NotificationDeliverySimulationException;

final class InMemoryNotificationDeliveryFake implements NotificationAuditOracle, NotificationCallbackSimulator, NotificationProvider, NotificationTimeOracle, NotificationWorkerOracle
{
    private const MAX_TIME_ADVANCE_MILLISECONDS = 86_400_000;

    /** @var array<string, NotificationDeliveryRequest> */
    private array $requests = [];

    /** @var array<string, NotificationSubmission> */
    private array $submissions = [];

    /** @var array<string, NotificationWorkerState> */
    private array $workerStates = [];

    /** @var array<string, list<NotificationDeliveryEvent>> */
    private array $events = [];

    /** @var array<string, array<string, array{outcome: NotificationCallbackOutcome, event: NotificationDeliveryEvent}>> */
    private array $callbacks = [];

    private int $currentMilliseconds = 0;

    public function submit(NotificationDeliveryRequest $request): NotificationSubmission
    {
        $existingRequest = $this->requests[$request->notificationKey] ?? null;
        if ($existingRequest !== null) {
            if ($existingRequest == $request) {
                return $this->submissions[$request->notificationKey];
            }

            throw NotificationDeliverySimulationException::because('notification_submission_conflict');
        }

        $submission = new NotificationSubmission(
            notificationKey: $request->notificationKey,
            providerReference: 'fake-'.$request->notificationKey,
            correlationId: $request->correlationId,
            acceptedAtMilliseconds: $this->currentMilliseconds,
        );
        $event = new NotificationDeliveryEvent(
            notificationKey: $request->notificationKey,
            providerReference: $submission->providerReference,
            callbackKey: null,
            type: NotificationDeliveryEventType::ProviderSubmitted,
            correlationId: $request->correlationId,
            occurredAtMilliseconds: $this->currentMilliseconds,
        );

        $this->requests[$request->notificationKey] = $request;
        $this->submissions[$request->notificationKey] = $submission;
        $this->workerStates[$request->notificationKey] = NotificationWorkerState::Queued;
        $this->events[$request->notificationKey] = [$event];
        $this->callbacks[$request->notificationKey] = [];

        return $submission;
    }

    public function simulate(
        string $notificationKey,
        string $callbackKey,
        NotificationCallbackOutcome $outcome,
    ): NotificationDeliveryEvent {
        $this->assertKnownNotification($notificationKey);
        $this->assertSafeKey($callbackKey);

        $existingCallback = $this->callbacks[$notificationKey][$callbackKey] ?? null;
        if ($existingCallback !== null) {
            if ($existingCallback['outcome'] === $outcome) {
                return $existingCallback['event'];
            }

            throw NotificationDeliverySimulationException::because('notification_callback_conflict');
        }

        if ($this->workerStates[$notificationKey] === NotificationWorkerState::Processed) {
            throw NotificationDeliverySimulationException::because('notification_callback_conflict');
        }

        $request = $this->requests[$notificationKey];
        $submission = $this->submissions[$notificationKey];
        $event = new NotificationDeliveryEvent(
            notificationKey: $notificationKey,
            providerReference: $submission->providerReference,
            callbackKey: $callbackKey,
            type: match ($outcome) {
                NotificationCallbackOutcome::Delivered => NotificationDeliveryEventType::CallbackDelivered,
                NotificationCallbackOutcome::Failed => NotificationDeliveryEventType::CallbackFailed,
            },
            correlationId: $request->correlationId,
            occurredAtMilliseconds: $this->currentMilliseconds,
        );

        $this->callbacks[$notificationKey][$callbackKey] = [
            'outcome' => $outcome,
            'event' => $event,
        ];
        $this->events[$notificationKey][] = $event;
        $this->workerStates[$notificationKey] = NotificationWorkerState::Processed;

        return $event;
    }

    public function workerState(string $notificationKey): NotificationWorkerState
    {
        $this->assertKnownNotification($notificationKey);

        return $this->workerStates[$notificationKey];
    }

    public function currentMilliseconds(): int
    {
        return $this->currentMilliseconds;
    }

    public function advanceMilliseconds(int $delta): void
    {
        if (
            $delta <= 0
            || $delta > self::MAX_TIME_ADVANCE_MILLISECONDS
            || $this->currentMilliseconds > PHP_INT_MAX - $delta
        ) {
            throw NotificationDeliverySimulationException::because('notification_time_invalid');
        }

        $this->currentMilliseconds += $delta;
    }

    public function events(string $notificationKey): array
    {
        $this->assertKnownNotification($notificationKey);

        return array_values($this->events[$notificationKey]);
    }

    private function assertKnownNotification(string $notificationKey): void
    {
        if (! isset($this->requests[$notificationKey])) {
            throw NotificationDeliverySimulationException::because('notification_not_found');
        }
    }

    private function assertSafeKey(string $value): void
    {
        if (
            strlen($value) > 64
            || preg_match('/\A[a-z0-9][a-z0-9._-]*\z/D', $value) !== 1
        ) {
            throw NotificationDeliverySimulationException::because('notification_request_invalid');
        }
    }
}
