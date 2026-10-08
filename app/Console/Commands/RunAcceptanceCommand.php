<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\RunOperationData;
use App\TestStatusEnum;
use Illuminate\Console\Command;

/** Version-2 client for one explicit hierarchy execution. */
final class RunAcceptanceCommand extends Command
{
    protected $signature = 'acceptance:run
        {app}
        {component}
        {suite}
        {scenario}
        {variant}
        {profile}
        {--browser=}
        {--headed}
        {--timeout=}
        {--slow-mo=}';

    public function handle(AcceptanceOperationService $service): int
    {
        $result = $service->execute(new OperationRequest('acceptance.run', [
            'app_key' => (string) $this->argument('app'),
            'component_key' => (string) $this->argument('component'),
            'suite_key' => (string) $this->argument('suite'),
            'scenario_key' => (string) $this->argument('scenario'),
            'variant_key' => (string) $this->argument('variant'),
            'profile_id' => $this->argument('profile'),
            'browser' => $this->option('browser'),
            'headed' => (bool) $this->option('headed'),
            'timeout_ms' => $this->option('timeout'),
            'slow_mo_ms' => $this->option('slow-mo'),
        ]));
        $data = $result->data;
        $payload = $data instanceof RunOperationData ? [
            'schema_version' => 2,
            'status' => $data->testStatus->value,
            'test_id' => $data->testId,
            'app_key' => $data->appKey,
            'component_key' => $data->componentKey,
            'suite_key' => $data->suiteKey,
            'scenario_key' => $data->scenarioKey,
            'variant_key' => $data->variantKey,
            'error_code' => $data->errorCode,
        ] : ['schema_version' => 2, 'status' => $result->status, 'error_code' => $result->errorCode];
        $this->line(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

        if ($data instanceof RunOperationData) {
            return $data->testStatus === TestStatusEnum::FINISHED ? self::SUCCESS : self::FAILURE;
        }

        return $result->status === 'rejected' ? self::INVALID : self::FAILURE;
    }
}
