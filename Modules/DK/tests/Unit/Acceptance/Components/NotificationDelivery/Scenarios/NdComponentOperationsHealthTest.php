<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit\Acceptance\Components\NotificationDelivery\Scenarios;

use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Modules\Core\Contracts\BrowserTargetObserver;
use Modules\Core\Contracts\BrowserTargetRuntime;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\BrowserTargetObservation;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Components\NotificationDelivery\Scenarios\NdComponentOperationsHealth;
use Modules\DK\Acceptance\Components\NotificationDelivery\Selectors\OperationsHealthSelectors;
use PHPUnit\Framework\TestCase;
use Playwright\Browser\BrowserContextInterface;
use Playwright\Locator\LocatorInterface;
use Playwright\Page\PageInterface;

final class NdComponentOperationsHealthTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_metadata_and_five_ordered_steps_are_exact_and_safe(): void
    {
        $scenario = new NdComponentOperationsHealth;
        $variant = (new OperationsHealthVariantCatalog)->require('v.e1b6f42e950f61e0');
        $context = $this->context($variant->expectedFacts, $variant->key);

        $steps = iterator_to_array($scenario->steps($context));

        self::assertSame('nd-component-operations-health', $scenario->key());
        self::assertSame(['browser'], $scenario->metadata()->capabilities);
        self::assertSame(['provider-io-none'], $scenario->metadata()->tags);
        self::assertSame(AutomationDisposition::AUTOMATED, $scenario->metadata()->disposition);
        self::assertSame(EvidenceMode::NON_SENSITIVE_VISUAL, $scenario->metadata()->evidenceMode);
        self::assertSame(
            ['prepare', 'reach component', 'exercise component lifecycle', 'observe safe results', 'cleanup'],
            array_column($steps, 'name'),
        );
        self::assertSame([true], array_values(array_unique(array_column($steps, 'passed'))));
        foreach ($steps as $step) {
            self::assertSame([], $step->results);
            self::assertNull($step->error);
            self::assertNull($step->errorCode);
        }
    }

    public function test_an_unknown_identity_is_rejected_before_navigation(): void
    {
        $browser = Mockery::mock(BrowserContextInterface::class);
        $page = Mockery::mock(PageInterface::class);
        $runtime = Mockery::mock(BrowserTargetRuntime::class);
        $browser->shouldReceive('setDefaultTimeout', 'setDefaultNavigationTimeout')->once();
        $runtime->shouldReceive('prepare')->once()->with($browser);
        $browser->shouldReceive('newPage')->once()->andReturn($page);
        $context = new TestContext(
            $browser,
            new AcceptanceExecutionIdentity(
                'dk', 'notification-delivery', 'admin.delivery',
                'nd-component-operations-health', 'v.ffffffffffffffff',
            ),
            $runtime,
            1_000,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('acceptance_configuration_invalid');

        iterator_to_array((new NdComponentOperationsHealth)->steps($context));
    }

    /** @param array<string, bool|int|float|string|null> $facts */
    private function context(array $facts, string $variantKey): TestContext
    {
        $browser = Mockery::mock(BrowserContextInterface::class);
        $page = Mockery::mock(PageInterface::class);
        $document = Mockery::mock(LocatorInterface::class);
        $focusTarget = Mockery::mock(LocatorInterface::class);
        $main = Mockery::mock(LocatorInterface::class);
        $runtime = new class($facts) implements BrowserTargetObserver, BrowserTargetRuntime
        {
            /** @param array<string, bool|int|float|string|null> $facts */
            public function __construct(private array $facts) {}

            public function prepare(BrowserContextInterface $browser): void {}

            public function open(PageInterface $page, string $surfaceKey): void
            {
                if ($surfaceKey !== OperationsHealthSelectors::SURFACE) {
                    throw new \RuntimeException('unexpected surface');
                }
            }

            public function observe(PageInterface $page, string $observationKey): BrowserTargetObservation
            {
                return new BrowserTargetObservation($observationKey, $this->facts);
            }
        };

        $browser->shouldReceive('setDefaultTimeout', 'setDefaultNavigationTimeout')->once();
        $browser->shouldReceive('newPage')->once()->andReturn($page);
        $page->shouldReceive('isClosed')->andReturn(false);
        $page->shouldReceive('setViewportSize')->once()->with(1440, 900);
        $page->shouldReceive('emulateMedia')->once()->with(['colorScheme' => 'light', 'reducedMotion' => 'no-preference']);
        $page->shouldReceive('evaluate')->andReturnUsing(static function (string $expression): array {
            if (str_contains($expression, 'window.innerWidth')) {
                return ['width' => 1440, 'height' => 900, 'deviceScaleFactor' => 1.0];
            }
            if (str_contains($expression, 'matchMedia')) {
                return [
                    'supported' => true,
                    'colorScheme' => 'light',
                    'reducedMotion' => 'no-preference',
                    'forcedColors' => false,
                ];
            }

            throw new \RuntimeException('unexpected page evaluation');
        });
        $page->shouldReceive('locator')->once()->with(OperationsHealthSelectors::document(
            (new OperationsHealthVariantCatalog)->require($variantKey),
        ))->andReturn($document);
        $page->shouldReceive('locator')->once()->with(OperationsHealthSelectors::FOCUS_TARGET)->andReturn($focusTarget);
        $page->shouldReceive('getByRole')->once()->with('main')->andReturn($main);

        $document->shouldReceive('count')->once()->andReturn(1);
        $document->shouldReceive('isVisible')->once()->andReturn(true);
        $document->shouldReceive('isEnabled')->once()->andReturn(true);

        $focusTarget->shouldReceive('count')->once()->andReturn(1);
        $focusTarget->shouldReceive('evaluate')->twice()->andReturn(
            ['supported' => true, 'focused' => false],
            ['supported' => true, 'focused' => true],
        );
        $focusTarget->shouldReceive('focus')->once();

        $main->shouldReceive('count')->twice()->andReturn(1);
        $main->shouldReceive('isVisible')->once()->andReturn(true);
        $main->shouldReceive('isEnabled')->once()->andReturn(true);
        $main->shouldReceive('boundingBox')->once()->andReturn([
            'x' => 0, 'y' => 0, 'width' => 1440, 'height' => 700,
        ]);
        $main->shouldReceive('evaluate')->once()->andReturn([
            'supported' => true,
            'visibleFraction' => 1.0,
            'clipped' => false,
            'overflowX' => false,
            'overflowY' => false,
        ]);

        return new TestContext(
            $browser,
            new AcceptanceExecutionIdentity(
                'dk', 'notification-delivery', 'admin.delivery',
                'nd-component-operations-health', $variantKey,
            ),
            $runtime,
            1_000,
        );
    }
}
