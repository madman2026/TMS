<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit\Acceptance\Shared\Target;

use App\Acceptance\Targets\Control\Data\TargetControlLeaseResult;
use App\Acceptance\Targets\Control\TargetControlProtocol;
use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetContext;
use LogicException;
use Modules\Core\Contracts\BrowserTargetObserver;
use Modules\Core\Contracts\BrowserTargetRuntime;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\OperationsHealthVariant;
use Modules\DK\Acceptance\Components\NotificationDelivery\Selectors\OperationsHealthSelectors;
use Modules\DK\Acceptance\Shared\Target\Contracts\DKSyntheticTargetControl;
use Modules\DK\Acceptance\Shared\Target\DKBrowserTargetRuntime;
use Modules\DK\Acceptance\Shared\Target\LocalLoopbackSecretHandleResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Playwright\Browser\BrowserContextInterface;
use Playwright\Page\PageInterface;

final class DKBrowserTargetRuntimeTest extends TestCase
{
    public function test_it_prepares_navigates_and_observes_only_the_allowlisted_surface(): void
    {
        $variant = (new OperationsHealthVariantCatalog)->require('v.b2ee9a53fab09e29');
        $context = $this->context();
        $control = $this->control($variant);
        $runtime = $this->runtime($control, $context, $variant);
        $browser = $this->createMock(BrowserContextInterface::class);
        $page = $this->createMock(PageInterface::class);
        $visited = [];

        $browser->expects(self::once())->method('setExtraHTTPHeaders')->with(self::callback(
            static fn (array $headers): bool => $headers['Accept-Language'] === 'fa'
                && $headers['X-Acceptance-Theme'] === 'light'
                && $headers['X-Acceptance-Lease-Context'] === 'opaque-context-a',
        ));
        $page->method('isClosed')->willReturn(false);
        $page->expects(self::exactly(2))->method('goto')->willReturnCallback(
            static function (string $url, array $options) use (&$visited): void {
                $visited[] = [$url, $options];
            },
        );
        $page->expects(self::once())->method('evaluate')->willReturn($this->navigationSnapshot());

        $runtime->prepare($browser);
        $runtime->open($page, OperationsHealthSelectors::SURFACE);
        $observation = $runtime->observe($page, OperationsHealthSelectors::OBSERVATION);

        self::assertInstanceOf(BrowserTargetRuntime::class, $runtime);
        self::assertInstanceOf(BrowserTargetObserver::class, $runtime);
        self::assertSame(OperationsHealthSelectors::OBSERVATION, $observation->key);
        self::assertSame($variant->expectedFacts, $observation->facts);
        self::assertSame([
            ['http://127.0.0.1:8000', ['waitUntil' => 'domcontentloaded']],
            ['http://127.0.0.1:8000/admin/notification-delivery/operations/health', ['waitUntil' => 'domcontentloaded']],
        ], $visited);
        self::assertSame([], $runtime->__debugInfo());
    }

    #[DataProvider('unsafeNavigationProvider')]
    public function test_navigation_href_must_remain_on_the_configured_origin(?string $href): void
    {
        $variant = (new OperationsHealthVariantCatalog)->require('v.b2ee9a53fab09e29');
        $runtime = $this->runtime($this->control($variant), $this->context(), $variant);
        $browser = $this->createMock(BrowserContextInterface::class);
        $page = $this->createMock(PageInterface::class);

        $browser->expects(self::once())->method('setExtraHTTPHeaders');
        $page->method('isClosed')->willReturn(false);
        $page->expects(self::once())->method('goto')->with('http://127.0.0.1:8000', ['waitUntil' => 'domcontentloaded']);
        $page->expects(self::once())->method('evaluate')->willReturn($this->navigationSnapshot($href));

        $runtime->prepare($browser);

        $this->expectException(AcceptanceExecutionException::class);
        $runtime->open($page, OperationsHealthSelectors::SURFACE);
    }

    /** @return array<string, array{?string}> */
    public static function unsafeNavigationProvider(): array
    {
        return [
            'missing' => [null],
            'relative' => ['/admin/notification-delivery/operations/health'],
            'different host' => ['http://localhost:8000/admin/notification-delivery/operations/health'],
            'different port' => ['http://127.0.0.1:8001/admin/notification-delivery/operations/health'],
            'credentials' => ['http://user@127.0.0.1:8000/admin/notification-delivery/operations/health'],
            'query' => ['http://127.0.0.1:8000/admin/notification-delivery/operations/health?unsafe=1'],
        ];
    }

    /** @return array<string, bool|int|string|null> */
    private function navigationSnapshot(?string $href = 'http://127.0.0.1:8000/admin/notification-delivery/operations/health'): array
    {
        return [
            'root_count' => 1,
            'root_expanded' => 'false',
            'group_count' => 1,
            'group_key' => 'cms-plugins-notification-delivery-monitoring-governance',
            'item_count' => 1,
            'href' => $href,
        ];
    }

    public function test_unknown_surface_and_serialization_fail_closed(): void
    {
        $variant = (new OperationsHealthVariantCatalog)->require('v.b2ee9a53fab09e29');
        $runtime = $this->runtime($this->control($variant), $this->context(), $variant);
        $browser = $this->createMock(BrowserContextInterface::class);
        $browser->expects(self::once())->method('setExtraHTTPHeaders');
        $runtime->prepare($browser);

        try {
            $runtime->open($this->createMock(PageInterface::class), 'notification-delivery.other');
            self::fail('Unsupported surfaces must fail closed.');
        } catch (AcceptanceExecutionException $exception) {
            self::assertSame('acceptance_configuration_invalid', $exception->errorCode);
        }

        $this->expectException(LogicException::class);
        serialize($runtime);
    }

    private function runtime(
        DKSyntheticTargetControl $control,
        TargetContext $context,
        OperationsHealthVariant $variant,
    ): DKBrowserTargetRuntime {
        return new DKBrowserTargetRuntime(
            $control,
            $context,
            $variant,
            'http://127.0.0.1:8000',
            LocalLoopbackSecretHandleResolver::HANDLE,
            new ResourceReference('acceptance-lease-context', 'opaque-context-a'),
        );
    }

    private function control(OperationsHealthVariant $variant): DKSyntheticTargetControl
    {
        return new class($variant) implements DKSyntheticTargetControl
        {
            public function __construct(private OperationsHealthVariant $variant) {}

            public function acquire(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle): TargetControlLeaseResult
            {
                throw new LogicException('not called');
            }

            public function inspect(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle): TargetControlLeaseResult
            {
                return new TargetControlLeaseResult(
                    TargetControlProtocol::IDENTITY,
                    $context->lifecycleId,
                    str_repeat('a', 64),
                    'active',
                    [new ResourceReference('fixture', 'fixture-a')],
                    new ResourceReference('acceptance-lease-context', 'opaque-context-a'),
                    OperationsHealthSelectors::OBSERVATION,
                    $this->variant->expectedFacts,
                );
            }

            public function cleanup(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle, int $timeoutMs): TargetControlLeaseResult
            {
                throw new LogicException('not called');
            }
        };
    }

    private function context(): TargetContext
    {
        return new TargetContext(
            'dcb1cf9d-207c-4a44-963b-000000000001',
            'dcb1cf9d-207c-4a44-963b-000000000002',
            null,
            'dk',
            'notification-delivery',
            'admin.delivery',
            'nd-component-operations-health',
            'v.b2ee9a53fab09e29',
            1,
        );
    }
}
