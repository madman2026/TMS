<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Shared\Target;

use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetContext;
use LogicException;
use Modules\Core\Contracts\BrowserTargetObserver;
use Modules\Core\Contracts\BrowserTargetRuntime;
use Modules\Core\Data\BrowserTargetObservation;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\OperationsHealthVariant;
use Modules\DK\Acceptance\Components\NotificationDelivery\Selectors\OperationsHealthSelectors;
use Modules\DK\Acceptance\Shared\Target\Contracts\DKSyntheticTargetControl;
use Playwright\Browser\BrowserContextInterface;
use Playwright\Page\PageInterface;

final class DKBrowserTargetRuntime implements BrowserTargetObserver, BrowserTargetRuntime
{
    private const LEASE_CONTEXT_HEADER = 'X-Acceptance-Lease-Context';

    private const THEME_HEADER = 'X-Acceptance-Theme';

    private bool $prepared = false;

    public function __construct(
        private readonly DKSyntheticTargetControl $control,
        private readonly TargetContext $context,
        private readonly OperationsHealthVariant $variant,
        private readonly string $endpoint,
        private readonly string $secretHandle,
        private readonly ResourceReference $leaseContext,
    ) {
        if ($leaseContext->type !== 'acceptance-lease-context') {
            throw AcceptanceExecutionException::configurationInvalid();
        }
    }

    public function prepare(BrowserContextInterface $browser): void
    {
        if ($this->prepared) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        $browser->setExtraHTTPHeaders([
            self::LEASE_CONTEXT_HEADER => $this->leaseContext->id,
            self::THEME_HEADER => $this->variant->theme,
            'Accept-Language' => $this->variant->locale,
        ]);
        $this->prepared = true;
    }

    public function open(PageInterface $page, string $surfaceKey): void
    {
        if (! $this->prepared || $surfaceKey !== OperationsHealthSelectors::SURFACE || $page->isClosed()) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        $page->goto($this->endpoint, ['waitUntil' => 'domcontentloaded']);
        $navigation = $this->navigationSnapshot($page);
        $href = $navigation['href'] ?? null;
        if (($navigation['root_count'] ?? null) !== 1
            || ! in_array($navigation['root_expanded'] ?? null, ['true', 'false'], true)
            || ($navigation['group_count'] ?? null) !== 1
            || ($navigation['group_key'] ?? null) !== 'cms-plugins-notification-delivery-monitoring-governance'
            || ($navigation['item_count'] ?? null) !== 1
            || ! is_string($href)) {
            throw AcceptanceExecutionException::configurationInvalid();
        }
        if (! is_string($href) || ! $this->isSameOriginNavigation($href)) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        $page->goto($href, ['waitUntil' => 'domcontentloaded']);
    }

    /** @return array<string, mixed> */
    private function navigationSnapshot(PageInterface $page): array
    {
        $snapshot = $page->evaluate(<<<'JS'
            () => {
                const root = document.querySelector('#cms-plugins-notification-delivery');
                const group = document.querySelector('[data-nd-menu-section="cms-plugins-notification-delivery-monitoring-governance"]');
                const item = document.querySelector('#cms-plugins-notification-delivery-health');

                return {
                    root_count: root ? 1 : 0,
                    root_expanded: root?.getAttribute('aria-expanded') ?? null,
                    group_count: group ? 1 : 0,
                    group_key: group?.getAttribute('data-nd-menu-section') ?? null,
                    item_count: item ? 1 : 0,
                    href: item?.href ?? null,
                };
            }
            JS);

        return is_array($snapshot) ? $snapshot : [];
    }

    private function isSameOriginNavigation(string $href): bool
    {
        $endpoint = parse_url($this->endpoint);
        $target = parse_url($href);

        return is_array($endpoint)
            && is_array($target)
            && ($target['scheme'] ?? null) === ($endpoint['scheme'] ?? null)
            && ($target['host'] ?? null) === ($endpoint['host'] ?? null)
            && ($target['port'] ?? null) === ($endpoint['port'] ?? null)
            && is_string($target['path'] ?? null)
            && str_starts_with($target['path'], '/')
            && strlen($target['path']) <= 256
            && array_intersect_key($target, array_flip(['user', 'pass', 'query', 'fragment'])) === [];
    }

    public function observe(PageInterface $page, string $observationKey): BrowserTargetObservation
    {
        if (! $this->prepared
            || $page->isClosed()
            || $observationKey !== OperationsHealthSelectors::OBSERVATION) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        $result = $this->control->inspect($this->context, $this->variant, $this->secretHandle);
        if ($result->state !== 'active'
            || $result->observationKey !== $observationKey
            || array_keys($result->facts) !== array_keys($this->variant->expectedFacts)) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        return new BrowserTargetObservation($observationKey, $result->facts);
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new LogicException('acceptance_runtime_non_serializable');
    }

    /** @param array<never, never> $data */
    public function __unserialize(array $data): void
    {
        throw new LogicException('acceptance_runtime_non_serializable');
    }

    /** @return array<never, never> */
    public function __debugInfo(): array
    {
        return [];
    }
}
