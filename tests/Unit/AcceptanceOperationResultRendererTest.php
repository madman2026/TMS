<?php

namespace Tests\Unit;

use App\Acceptance\Operations\Data\FileChange;
use App\Acceptance\Operations\Data\ModuleChangeData;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Targets\Data\CleanupResult;
use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetEnvironment;
use App\Acceptance\Targets\Data\TargetExecutionOutcome;
use App\Acceptance\Targets\Data\TargetOracleResult;
use App\Acceptance\Targets\Data\TargetReadinessResult;
use App\Acceptance\Targets\Data\TargetResourceLifecycleData;
use App\Console\Acceptance\OperationResultRenderer;
use Illuminate\Console\Command;
use Mockery;
use Tests\TestCase;

class AcceptanceOperationResultRendererTest extends TestCase
{
    public function test_new_operations_use_the_ordered_version_two_envelope_and_map_data_explicitly(): void
    {
        $data = new ModuleChangeData(
            'acceptance_app',
            'Fixture',
            'fixture',
            null,
            true,
            [new FileChange('Modules/Fixture/app.php', 'create')],
        );
        $result = new OperationResult(
            'acceptance.app.create',
            'succeeded',
            null,
            'dcb1cf9d-207c-4a44-963b-000000000009',
            'dcb1cf9d-207c-4a44-963b-000000000010',
            data: $data,
        );

        [$exit, $json] = $this->render($result);
        $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertSame([
            'schema_version', 'operation', 'status', 'error_code', 'correlation_id', 'operation_id',
            'retryable', 'permanent', 'admin_action_required', 'errors', 'data',
        ], array_keys($payload));
        $this->assertStringContainsString('"errors":{}', $json);
        $this->assertSame('Modules/Fixture/app.php', $payload['data']['changes'][0]['path']);
        $this->assertSame('create', $payload['data']['changes'][0]['action']);
    }

    public function test_resource_projection_never_exposes_raw_resource_ids(): void
    {
        $reference = new ResourceReference('account', 'opaque-a');
        $resources = new TargetResourceLifecycleData(
            'dcb1cf9d-207c-4a44-963b-000000000011',
            'dcb1cf9d-207c-4a44-963b-000000000009',
            null,
            'app-a',
            'component-a',
            'suite-a',
            'scenario-a',
            'variant-a',
            1,
            'succeeded',
            'complete',
            TargetReadinessResult::ready(TargetEnvironment::TESTING),
            [$reference],
            TargetExecutionOutcome::succeeded(),
            TargetOracleResult::passed(),
            CleanupResult::success([$reference]),
            null,
            null,
        );
        $result = new OperationResult(
            'acceptance.synthetic',
            'succeeded',
            null,
            'dcb1cf9d-207c-4a44-963b-000000000009',
            data: $resources,
        );

        [, $json] = $this->render($result);
        $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString('opaque-a', $json);
        $this->assertSame('account', $payload['data']['references'][0]['type']);
        $this->assertSame(hash('sha256', "account\0opaque-a"), $payload['data']['references'][0]['reference_hash']);
    }

    public function test_client_failures_are_bounded_and_use_reserved_exit_codes(): void
    {
        $renderer = new OperationResultRenderer;
        $json = '';
        $command = Mockery::mock(Command::class);
        $command->shouldReceive('line')->twice()->withArgs(function (string $line) use (&$json): bool {
            $json = $line;

            return true;
        });

        $this->assertSame(Command::INVALID, $renderer->renderClientFailure($command, 'cli_input_invalid', false));
        $this->assertSame('cli_input_invalid', json_decode($json, true, flags: JSON_THROW_ON_ERROR)['error_code']);
        $this->assertSame(130, $renderer->renderClientFailure($command, 'cli_interrupted', false));
        $this->assertSame('cli_interrupted', json_decode($json, true, flags: JSON_THROW_ON_ERROR)['error_code']);
    }

    /** @return array{int, string} */
    private function render(OperationResult $result): array
    {
        $json = '';
        $command = Mockery::mock(Command::class);
        $command->shouldReceive('line')->once()->withArgs(function (string $line) use (&$json): bool {
            $json = $line;

            return true;
        });

        return [(new OperationResultRenderer)->render($command, $result, false), $json];
    }
}
