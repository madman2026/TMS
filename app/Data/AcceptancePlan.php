<?php

namespace App\Data;

use App\Exceptions\AcceptanceCatalogException;
use Modules\Core\Enums\AutomationDisposition;

/** Bounded observed selection; no global catalog or source-revision guarantee. */
final readonly class AcceptancePlan
{
    public const VERSION = 2;

    /** @var list<AcceptancePlanItem> */
    public array $items;

    /** @var array<string, string> */
    public array $catalogVersions;

    public string $fingerprint;

    public function __construct(public AcceptanceSelector $selector, array $items, array $catalogVersions)
    {
        $copy = [];
        foreach ($items as $item) {
            if (! $item instanceof AcceptancePlanItem) {
                throw AcceptanceCatalogException::because('acceptance_catalog_invalid');
            }
            $copy[] = $item;
        }
        if (count($copy) > $selector->limit) {
            throw AcceptanceCatalogException::because('acceptance_catalog_limit_exceeded');
        }
        usort($copy, fn (AcceptancePlanItem $a, AcceptancePlanItem $b): int => strcmp($a->identity(), $b->identity()));
        $this->items = $copy;

        $versions = [];
        foreach ($catalogVersions as $app => $version) {
            $versions[ScenarioDescriptor::assertKey((string) $app)] = ScenarioDescriptor::assertKey($version);
        }
        ksort($versions, SORT_STRING);
        $this->catalogVersions = $versions;
        $this->fingerprint = hash('sha256', json_encode($this->canonicalPayload(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }

    /** Exposed for testing the allowlist that enters the hash, not raw provider state. */
    public function canonicalPayload(): array
    {
        return [
            'plan_version' => self::VERSION,
            'selector' => $this->selector->normalized(),
            'catalog_versions' => (object) $this->catalogVersions,
            'items' => array_map(fn (AcceptancePlanItem $item): array => $item->toArray(), $this->items),
        ];
    }

    public function toArray(bool $planning = true): array
    {
        $counts = array_fill_keys(array_column(AutomationDisposition::cases(), 'value'), 0);
        $rows = [];
        foreach ($this->items as $item) {
            $counts[$item->metadata->disposition->value]++;
            if (! $planning || $item->executable()) {
                $rows[] = $item->toArray();
            }
        }

        return [
            'status' => $planning ? 'planned' : 'listed',
            'plan_version' => self::VERSION,
            'catalog_versions' => (object) $this->catalogVersions,
            'counts' => [
                'matched' => count($this->items),
                'executable' => $counts['automated'],
                'excluded' => count($this->items) - $counts['automated'],
                'by_disposition' => $counts,
            ],
            'fingerprint' => $this->fingerprint,
            'items' => $rows,
        ];
    }
}
