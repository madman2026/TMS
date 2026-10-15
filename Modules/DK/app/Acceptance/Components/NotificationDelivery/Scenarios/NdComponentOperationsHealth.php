<?php

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Scenarios;

use App\Contracts\BaseAction;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Modules\Core\Traits\HasStep;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\OperationsHealthVariant;
use Modules\DK\Acceptance\Components\NotificationDelivery\Selectors\OperationsHealthSelectors;
use RuntimeException;

final class NdComponentOperationsHealth extends BaseAction implements AcceptanceScenario
{
    use HasStep;

    public function key(): string
    {
        return 'nd-component-operations-health';
    }

    public function name(): string
    {
        return 'nd-component-operations-health';
    }

    public function metadata(): ScenarioMetadata
    {
        return new ScenarioMetadata(
            ['browser'],
            ['provider-io-none'],
            AutomationDisposition::AUTOMATED,
            EvidenceMode::NON_SENSITIVE_VISUAL,
        );
    }

    public function steps(TestContext $context): iterable
    {
        $variant = $this->variant($context);

        yield $this->step('prepare', function () use ($context, $variant): array {
            if ($context->page->isClosed() || $variant->key !== $context->identity->variantKey) {
                throw new RuntimeException('acceptance_configuration_invalid');
            }

            return [];
        });

        yield $this->step('reach component', function () use ($context): array {
            $context->openTargetSurface(OperationsHealthSelectors::SURFACE);

            return [];
        });

        yield $this->step('exercise component lifecycle', function () use ($context, $variant): array {
            $viewport = $context->browserProbe
                ->resizeViewport($variant->viewportWidth, $variant->viewportHeight)
                ->requireAvailable();
            if ($viewport['width'] !== $variant->viewportWidth || $viewport['height'] !== $variant->viewportHeight) {
                throw new RuntimeException('browser_probe_invalid_payload');
            }

            $media = $context->browserProbe->emulateMedia($variant->theme, 'no-preference')->requireAvailable();
            if ($media['colorScheme'] !== $variant->theme) {
                throw new RuntimeException('browser_probe_invalid_payload');
            }

            $document = $context->page->locator(OperationsHealthSelectors::document($variant));
            $visibility = $context->browserProbe->visibility($document)->requireAvailable();
            if (! $visibility['visible']) {
                throw new RuntimeException('browser_probe_invalid_payload');
            }

            if ($variant->focused) {
                $focusTarget = $context->page->locator(OperationsHealthSelectors::FOCUS_TARGET);
                $focus = $context->browserProbe->focus($focusTarget)->requireAvailable();
                if (! $focus['focused']) {
                    throw new RuntimeException('browser_probe_invalid_payload');
                }
            }

            return [];
        });

        yield $this->step('observe safe results', function () use ($context, $variant): array {
            $main = $context->page->getByRole('main');
            $visibility = $context->browserProbe->visibility($main)->requireAvailable();
            $layout = $context->browserProbe->layout($main)->requireAvailable();
            if (! $visibility['visible']
                || $layout['width'] <= 0
                || $layout['overflowX']) {
                throw new RuntimeException('browser_probe_invalid_payload');
            }

            $observation = $context->observeTarget(OperationsHealthSelectors::OBSERVATION);
            if ($observation->facts !== $variant->expectedFacts) {
                throw new RuntimeException('acceptance_configuration_invalid');
            }

            return [];
        });

        yield $this->step('cleanup', function () use ($context): array {
            if ($context->page->isClosed()) {
                throw new RuntimeException('browser_probe_page_closed');
            }

            return [];
        });
    }

    private function variant(TestContext $context): OperationsHealthVariant
    {
        $identity = $context->identity;
        if ($identity->appKey !== 'dk'
            || $identity->componentKey !== 'notification-delivery'
            || $identity->suiteKey !== 'admin.delivery'
            || $identity->scenarioKey !== $this->key()) {
            throw new RuntimeException('acceptance_configuration_invalid');
        }

        return (new OperationsHealthVariantCatalog)->require($identity->variantKey);
    }
}
