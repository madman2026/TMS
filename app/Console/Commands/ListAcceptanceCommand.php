<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Data\CatalogOperationData;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Data\AcceptancePlanItem;
use App\Data\AcceptanceSelector;
use Illuminate\Console\Command;

/** JSON-only inspection; the planner never enters the execution pipeline. */
class ListAcceptanceCommand extends Command
{
    protected $signature = 'acceptance:list '.AcceptanceSelector::OPTIONS;

    public function handle(AcceptanceOperationService $service): int
    {
        $parameters = [];
        foreach (['app', 'component', 'suite', 'scenario', 'variant', 'capability', 'tag', 'disposition', 'limit'] as $option) {
            $parameters[$option] = $this->option($option);
        }
        $parameters['evidence_mode'] = $this->option('evidence-mode');
        $result = $service->execute(new OperationRequest($this->planning() ? 'acceptance.plan' : 'acceptance.list', $parameters));
        if ($result->status !== 'succeeded') {
            $this->line(json_encode([
                'schema_version' => 2,
                'status' => $result->status,
                'error_code' => $result->errorCode,
            ], JSON_THROW_ON_ERROR));

            return $result->status === 'rejected' ? self::INVALID : self::FAILURE;
        }
        /** @var CatalogOperationData $data */
        $data = $result->data;
        // The accepted JSON map and field-order conventions belong only to this client.
        $this->line(json_encode([
            'schema_version' => 2,
            'status' => $this->planning() ? 'planned' : 'listed',
            'plan_version' => $data->planVersion,
            'catalog_versions' => (object) $data->catalogVersions,
            'counts' => [
                'matched' => $data->matched, 'executable' => $data->executable,
                'excluded' => $data->excluded, 'by_disposition' => $data->byDisposition,
            ],
            'fingerprint' => $data->fingerprint,
            'items' => array_map(fn (AcceptancePlanItem $item): array => $item->toArray(), $data->items),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }

    protected function planning(): bool
    {
        return false;
    }
}
