<?php

namespace App\Console\Commands;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\RunOperationData;
use App\TestStatusEnum;
use Illuminate\Console\Command;

/** Input and legacy presentation adapter for the shared run operation. */
final class RunAcceptanceCommand extends Command
{
    protected $signature = 'acceptance:run
        {app}
        {scenario}
        {profile}
        {--browser=}
        {--headed}
        {--timeout=}
        {--slow-mo=}';

    public function handle(AcceptanceOperationService $service): int
    {
        $result = $service->execute(new OperationRequest('acceptance.run', [
            'app_key' => (string) $this->argument('app'),
            'scenario_key' => (string) $this->argument('scenario'),
            'profile_id' => $this->argument('profile'),
            'browser' => $this->option('browser'),
            'headed' => (bool) $this->option('headed'),
            'timeout_ms' => $this->option('timeout'),
            'slow_mo_ms' => $this->option('slow-mo'),
        ]));
        $data = $result->data;
        $payload = $data instanceof RunOperationData ? [
            'status' => $data->testStatus->value,
            'test_id' => $data->testId,
            'app_key' => $data->appKey,
            'scenario_key' => $data->scenarioKey,
            'error_code' => $data->errorCode,
        ] : ['status' => $result->status, 'error_code' => $result->errorCode];
        $this->line(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

        if ($data instanceof RunOperationData) {
            return $data->testStatus === TestStatusEnum::FINISHED ? self::SUCCESS : self::FAILURE;
        }

        return $result->status === 'rejected' ? self::INVALID : self::FAILURE;
    }
}
