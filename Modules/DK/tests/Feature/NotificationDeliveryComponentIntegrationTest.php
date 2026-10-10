<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Feature;

use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceCatalog;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationAuditOracle;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationCallbackSimulator;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationProvider;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationTimeOracle;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationWorkerOracle;
use Modules\DK\Acceptance\Components\NotificationDelivery\Fakes\InMemoryNotificationDeliveryFake;
use Tests\TestCase;

final class NotificationDeliveryComponentIntegrationTest extends TestCase
{
    public function test_container_and_catalog_expose_one_empty_side_effect_free_component(): void
    {
        Http::preventStrayRequests();
        Notification::fake();
        Queue::fake();
        Event::fake();

        $provider = $this->app->make(NotificationProvider::class);
        $callback = $this->app->make(NotificationCallbackSimulator::class);
        $worker = $this->app->make(NotificationWorkerOracle::class);
        $time = $this->app->make(NotificationTimeOracle::class);
        $audit = $this->app->make(NotificationAuditOracle::class);
        $catalog = $this->app->make(AcceptanceCatalog::class);

        $this->assertInstanceOf(InMemoryNotificationDeliveryFake::class, $provider);
        $this->assertSame($provider, $callback);
        $this->assertSame($provider, $worker);
        $this->assertSame($provider, $time);
        $this->assertSame($provider, $audit);
        $this->assertSame($provider, $this->app->make(NotificationProvider::class));

        $this->assertSame(['dk'], $catalog->appKeys());
        $this->assertSame('v2', $catalog->version('dk'));
        $this->assertSame(
            ['notification-delivery'],
            array_map(static fn ($component): string => $component->key, iterator_to_array($catalog->components('dk'))),
        );
        $this->assertSame([], iterator_to_array($catalog->suites('dk')));
        $this->assertSame([], iterator_to_array($catalog->descriptors('dk')));
        $this->assertSame([], iterator_to_array($catalog->variants('dk', 'missing-scenario')));
        $this->assertSame([], iterator_to_array($this->app->make(AcceptanceAppRegistry::class)->app('dk')->sourceCaseMappings()));
        $this->assertNull($this->app->make(AcceptanceAppRegistry::class)->app('dk')->resolveScenario(
            'notification-delivery',
            'missing-suite',
            'missing-scenario',
            'missing-variant',
        ));
        $this->assertSame(0, $time->currentMilliseconds());

        Notification::assertNothingSent();
        Queue::assertNothingPushed();
        Event::assertNothingDispatched();
    }
}
