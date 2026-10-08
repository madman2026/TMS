<?php

namespace App\Acceptance\Operations\Data;

use App\Data\AcceptancePlan;
use App\Data\AcceptancePlanItem;
use App\Data\ScenarioDescriptor;
use InvalidArgumentException;
use Modules\Core\Enums\AutomationDisposition;

/** Immutable catalog meaning; display statuses and map encoding belong to clients. */
final readonly class CatalogOperationData
{
    /** @var array<string, string> */
    public array $catalogVersions;

    /** @var array<string, int> */
    public array $byDisposition;

    /** @var list<AcceptancePlanItem> */
    public array $items;

    public function __construct(
        public int $planVersion,
        array $catalogVersions,
        public int $matched,
        public int $executable,
        public int $excluded,
        array $byDisposition,
        public string $fingerprint,
        array $items,
    ) {
        $versions = [];
        foreach ($catalogVersions as $app => $version) {
            $versions[ScenarioDescriptor::assertKey((string) $app)] = ScenarioDescriptor::assertKey($version);
        }
        $counts = [];
        foreach (AutomationDisposition::cases() as $disposition) {
            $count = $byDisposition[$disposition->value] ?? null;
            if (! is_int($count) || $count < 0) {
                throw new InvalidArgumentException('operation_result_invalid');
            }
            $counts[$disposition->value] = $count;
        }
        if (array_diff_key($byDisposition, $counts) !== [] || $planVersion !== AcceptancePlan::VERSION
            || $matched !== array_sum($counts) || $executable !== $counts['automated']
            || $excluded !== $matched - $executable || $matched > 1000
            || ! preg_match('/^[a-f0-9]{64}$/D', $fingerprint)) {
            throw new InvalidArgumentException('operation_result_invalid');
        }
        $rows = [];
        foreach ($items as $item) {
            if (! $item instanceof AcceptancePlanItem) {
                throw new InvalidArgumentException('operation_result_invalid');
            }
            $rows[] = $item;
        }
        if (count($rows) > $matched) {
            throw new InvalidArgumentException('operation_result_invalid');
        }
        $this->catalogVersions = $versions;
        $this->byDisposition = $counts;
        $this->items = $rows;
    }

    /** Snapshot existing planner data without invoking its client projection. */
    public static function fromPlan(AcceptancePlan $plan, bool $planning): self
    {
        $counts = array_fill_keys(array_column(AutomationDisposition::cases(), 'value'), 0);
        $items = [];
        foreach ($plan->items as $item) {
            $counts[$item->metadata->disposition->value]++;
            if (! $planning || $item->executable()) {
                $items[] = $item;
            }
        }

        return new self(
            AcceptancePlan::VERSION, $plan->catalogVersions, count($plan->items),
            $counts['automated'], count($plan->items) - $counts['automated'],
            $counts, $plan->fingerprint, $items,
        );
    }
}
