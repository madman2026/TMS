<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Feature;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Data\TargetContext;
use App\Acceptance\Targets\Data\TargetExecutionOutcome;
use App\Acceptance\Targets\Data\TargetResourceLifecycleData;
use App\Acceptance\Targets\TargetResourceAdapters;
use App\Acceptance\Targets\TargetResourceCoordinator;
use App\Acceptance\Targets\TargetResourceRegistry;
use App\Data\ComponentDescriptor;
use App\Services\AcceptanceAppRegistry;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Mockery;
use Modules\DK\Acceptance\DKAcceptanceApp;
use Modules\DK\Acceptance\Shared\Target\DKTargetProfile;
use Modules\DK\Acceptance\Shared\Target\DKTargetResourceAdapter;
use Tests\TestCase;

class DKTargetFoundationIntegrationTest extends TestCase
{
    public function test_default_application_registers_one_fail_closed_dk_target_aggregate(): void
    {
        $registry = $this->app->make(TargetResourceRegistry::class);
        $aggregate = $registry->for('dk');

        $this->assertInstanceOf(TargetResourceAdapters::class, $aggregate);
        $this->assertInstanceOf(DKTargetResourceAdapter::class, $aggregate->readiness);
        $this->assertSame($aggregate->readiness, $aggregate->accounts);
        $this->assertSame($aggregate->readiness, $aggregate->fixtures);
        $this->assertSame($aggregate->readiness, $aggregate->oracle);
        $this->assertSame($aggregate->readiness, $aggregate->cleanup);
        $this->assertSame($registry, $this->app->make(TargetResourceRegistry::class));
        $this->assertSame($aggregate, $this->app->make(TargetResourceRegistry::class)->for('dk'));

        $profile = $this->app->make(DKTargetProfile::class);
        $this->assertFalse($profile->enabled);
        $this->assertSame('unknown', $profile->environment->value);
        $this->assertSame([
            'enabled' => false,
            'environment' => 'unknown',
        ], $this->app->make('config')->get('dk.target'));

        $appRegistry = $this->app->make(AcceptanceAppRegistry::class);
        $this->assertSame(['dk'], $appRegistry->appKeys());
        $this->assertInstanceOf(DKAcceptanceApp::class, $appRegistry->app('dk'));
        $this->assertSame(
            ['notification-delivery'],
            array_map(
                static fn (ComponentDescriptor $component): string => $component->key,
                iterator_to_array($appRegistry->app('dk')->components()),
            ),
        );

        try {
            $registry->register('dk', $aggregate);
            $this->fail('Expected the DK target aggregate registration to reject duplication.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('target_resource_registry_duplicate', $exception->getMessage());
        }
    }

    public function test_lifecycle_stops_before_execution_for_every_foundation_profile_state(): void
    {
        Log::swap(Mockery::spy(LogManager::class));
        $executionCalls = 0;

        $default = $this->executeWithProfile(null, null, $executionCalls);
        $disabled = $this->executeWithProfile(false, 'testing', $executionCalls);
        $enabled = $this->executeWithProfile(true, 'staging', $executionCalls);

        $this->assertSame('failed', $default->status);
        $this->assertSame('readiness', $default->stage);
        $this->assertSame('unsafe_target', $default->primaryErrorCode);
        $this->assertFalse($default->readiness?->retryable);
        $this->assertTrue($default->readiness?->permanent);
        $this->assertTrue($default->readiness?->adminActionRequired);
        $this->assertSame([], $default->references);
        $this->assertNull($default->execution);
        $this->assertNull($default->oracle);
        $this->assertNull($default->cleanup);

        $this->assertSame('failed', $disabled->status);
        $this->assertSame('readiness', $disabled->stage);
        $this->assertSame('target_not_ready', $disabled->primaryErrorCode);
        $this->assertTrue($disabled->readiness?->retryable);
        $this->assertFalse($disabled->readiness?->permanent);
        $this->assertFalse($disabled->readiness?->adminActionRequired);
        $this->assertSame([], $disabled->references);
        $this->assertNull($disabled->execution);
        $this->assertNull($disabled->oracle);
        $this->assertNull($disabled->cleanup);

        $this->assertSame('failed', $enabled->status);
        $this->assertSame('account', $enabled->stage);
        $this->assertSame('resource_unavailable', $enabled->primaryErrorCode);
        $this->assertTrue($enabled->readiness?->ready);
        $this->assertSame([], $enabled->references);
        $this->assertNull($enabled->execution);
        $this->assertNull($enabled->oracle);
        $this->assertNull($enabled->cleanup);

        $this->assertSame(0, $executionCalls);
        $this->assertSame($default->lifecycleId, $disabled->lifecycleId);
        $this->assertSame($default->correlationId, $enabled->correlationId);
        $this->assertSame('dk', $enabled->appKey);
        $this->assertStringNotContainsString('app-secret://', json_encode([$default, $disabled, $enabled], JSON_THROW_ON_ERROR));

        foreach (['log', 'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'] as $method) {
            Log::shouldNotHaveReceived($method);
        }
    }

    private function executeWithProfile(
        ?bool $enabled,
        ?string $environment,
        int &$executionCalls,
    ): TargetResourceLifecycleData {
        if ($enabled !== null && $environment !== null) {
            $this->app->make('config')->set('dk.target', [
                'enabled' => $enabled,
                'environment' => $environment,
            ]);
        }

        $this->app->forgetInstance(DKTargetProfile::class);
        $this->app->forgetInstance(DKTargetResourceAdapter::class);
        $this->app->forgetInstance(TargetResourceRegistry::class);

        return $this->app->make(TargetResourceCoordinator::class)->execute(
            $this->context(),
            $this->prerequisites(),
            function () use (&$executionCalls): TargetExecutionOutcome {
                $executionCalls++;

                return TargetExecutionOutcome::succeeded();
            },
        );
    }

    private function context(): TargetContext
    {
        return new TargetContext(
            'dcb1cf9d-207c-4a44-963b-000000000001',
            'dcb1cf9d-207c-4a44-963b-000000000002',
            null,
            'dk', 'foundation', 'foundation', 'foundation', 'default', 1,
        );
    }

    private function prerequisites(): PrerequisiteExecutionData
    {
        return new PrerequisiteExecutionData(
            null, 'dk', 'foundation', 'foundation', 'foundation', 'default', 1,
            secretReferences: ['credential' => 'app-secret://dk/opaque-a'],
        );
    }
}
