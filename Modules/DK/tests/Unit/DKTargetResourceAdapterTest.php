<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Control\Data\TargetControlLeaseResult;
use App\Acceptance\Targets\Control\TargetControlProtocol;
use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetContext;
use App\Acceptance\Targets\Data\TargetEnvironment;
use App\Acceptance\Targets\Data\TargetExecutionOutcome;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\OperationsHealthVariant;
use Modules\DK\Acceptance\Components\NotificationDelivery\Fixtures\OperationsHealthFixture;
use Modules\DK\Acceptance\Shared\Target\Contracts\DKSyntheticTargetControl;
use Modules\DK\Acceptance\Shared\Target\DKTargetProfile;
use Modules\DK\Acceptance\Shared\Target\DKTargetResourceAdapter;
use Modules\DK\Acceptance\Shared\Target\LocalLoopbackSecretHandleResolver;
use PHPUnit\Framework\TestCase;

class DKTargetResourceAdapterTest extends TestCase
{
    public function test_readiness_uses_profile_safety_and_enabled_state(): void
    {
        $testingDisabled = $this->adapter(false, TargetEnvironment::TESTING)->probe($this->context());
        $stagingEnabled = $this->adapter(true, TargetEnvironment::STAGING)->probe($this->context());
        $productionEnabled = $this->adapter(true, TargetEnvironment::PRODUCTION)->probe($this->context());
        $unknownEnabled = $this->adapter(true, TargetEnvironment::UNKNOWN)->probe($this->context());

        $this->assertFalse($testingDisabled->ready);
        $this->assertSame('target_not_ready', $testingDisabled->errorCode);
        $this->assertTrue($testingDisabled->retryable);
        $this->assertFalse($testingDisabled->permanent);
        $this->assertFalse($testingDisabled->adminActionRequired);

        $this->assertTrue($stagingEnabled->ready);
        $this->assertNull($stagingEnabled->errorCode);
        $this->assertNull($stagingEnabled->retryable);
        $this->assertNull($stagingEnabled->permanent);
        $this->assertFalse($stagingEnabled->adminActionRequired);

        foreach ([$productionEnabled, $unknownEnabled] as $unsafe) {
            $this->assertFalse($unsafe->ready);
            $this->assertSame('unsafe_target', $unsafe->errorCode);
            $this->assertFalse($unsafe->retryable);
            $this->assertTrue($unsafe->permanent);
            $this->assertTrue($unsafe->adminActionRequired);
        }
    }

    public function test_non_readiness_ports_reject_without_mutating_references(): void
    {
        $adapter = $this->adapter(true, TargetEnvironment::TESTING);
        $context = $this->context();
        $prerequisites = $this->prerequisites();
        $reference = new ResourceReference('fixture', 'opaque-a');
        $contextWithReference = $context->withReferences([$reference]);

        $account = $adapter->resolve($context, $prerequisites);
        $fixture = $adapter->provision($context, $prerequisites);
        $oracle = $adapter->evaluate($context, TargetExecutionOutcome::succeeded());
        $emptyCleanup = $adapter->cleanup($context, 30_000);
        $rejectedCleanup = $adapter->cleanup($contextWithReference, 30_000);

        $this->assertFalse($account->succeeded);
        $this->assertSame([], $account->references);
        $this->assertSame('resource_unavailable', $account->errorCode);
        $this->assertFalse($account->retryable);
        $this->assertTrue($account->permanent);
        $this->assertTrue($account->adminActionRequired);

        $this->assertFalse($fixture->succeeded);
        $this->assertSame([], $fixture->references);
        $this->assertSame('fixture_setup_failed', $fixture->errorCode);
        $this->assertTrue($fixture->retryable);
        $this->assertFalse($fixture->permanent);
        $this->assertTrue($fixture->adminActionRequired);

        $this->assertFalse($oracle->passed);
        $this->assertSame('oracle_failed', $oracle->errorCode);
        $this->assertFalse($oracle->retryable);
        $this->assertTrue($oracle->permanent);
        $this->assertFalse($oracle->adminActionRequired);

        $this->assertTrue($emptyCleanup->succeeded);
        $this->assertSame([], $emptyCleanup->cleanedReferences);
        $this->assertSame([], $emptyCleanup->remainingReferences);

        $this->assertFalse($rejectedCleanup->succeeded);
        $this->assertSame([], $rejectedCleanup->cleanedReferences);
        $this->assertSame([$reference], $rejectedCleanup->remainingReferences);
        $this->assertSame('cleanup_failed', $rejectedCleanup->errorCode);
        $this->assertTrue($rejectedCleanup->retryable);
        $this->assertFalse($rejectedCleanup->permanent);
        $this->assertTrue($rejectedCleanup->adminActionRequired);
        $this->assertSame([$reference], $contextWithReference->references);
    }

    public function test_selected_operations_health_lifecycle_resolves_provisions_oracles_and_cleans(): void
    {
        $control = new class implements DKSyntheticTargetControl
        {
            /** @var list<string> */
            public array $calls = [];

            public function acquire(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle): TargetControlLeaseResult
            {
                $this->calls[] = 'acquire';

                return new TargetControlLeaseResult(
                    TargetControlProtocol::IDENTITY,
                    $context->lifecycleId,
                    str_repeat('a', 64),
                    'active',
                    [new ResourceReference('fixture', 'fixture-a')],
                    new ResourceReference('acceptance-lease-context', 'opaque-context-a'),
                );
            }

            public function inspect(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle): TargetControlLeaseResult
            {
                throw new \LogicException('not called');
            }

            public function cleanup(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle, int $timeoutMs): TargetControlLeaseResult
            {
                $this->calls[] = 'cleanup';

                return new TargetControlLeaseResult(
                    TargetControlProtocol::IDENTITY,
                    $context->lifecycleId,
                    str_repeat('a', 64),
                    'cleaned',
                    [],
                );
            }
        };
        $adapter = $this->adapter(true, TargetEnvironment::TESTING, $control);
        $context = $this->operationsHealthContext();
        $prerequisites = $this->operationsHealthPrerequisites();

        $account = $adapter->resolve($context, $prerequisites);
        $accountContext = $context->withReferences($account->references);
        $fixture = $adapter->provision($accountContext, $prerequisites);
        $finalContext = $accountContext->withReferences($fixture->references);
        $oracle = $adapter->evaluate($finalContext, TargetExecutionOutcome::succeeded());
        $cleanup = $adapter->cleanup($finalContext, 30_000);

        $this->assertTrue($account->succeeded);
        $this->assertSame(['account'], array_column($account->references, 'type'));
        $this->assertTrue($fixture->succeeded);
        $this->assertSame(['fixture', 'acceptance-lease-context'], array_column($fixture->references, 'type'));
        $this->assertTrue($oracle->passed);
        $this->assertTrue($cleanup->succeeded);
        $this->assertSame($finalContext->references, $cleanup->cleanedReferences);
        $this->assertSame(['acquire', 'cleanup'], $control->calls);
    }

    private function adapter(
        bool $enabled,
        TargetEnvironment $environment,
        ?DKSyntheticTargetControl $control = null,
    ): DKTargetResourceAdapter {
        $control ??= new class implements DKSyntheticTargetControl
        {
            public function acquire(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle): TargetControlLeaseResult
            {
                throw new \LogicException('not called');
            }

            public function inspect(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle): TargetControlLeaseResult
            {
                throw new \LogicException('not called');
            }

            public function cleanup(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle, int $timeoutMs): TargetControlLeaseResult
            {
                throw new \LogicException('not called');
            }
        };
        $variants = new OperationsHealthVariantCatalog;

        return new DKTargetResourceAdapter(
            new DKTargetProfile($enabled, $environment),
            new OperationsHealthFixture($control, $variants, LocalLoopbackSecretHandleResolver::HANDLE),
            $variants,
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
        );
    }

    private function operationsHealthContext(): TargetContext
    {
        return new TargetContext(
            'dcb1cf9d-207c-4a44-963b-000000000001',
            'dcb1cf9d-207c-4a44-963b-000000000002',
            null,
            'dk', 'notification-delivery', 'admin.delivery',
            'nd-component-operations-health', 'v.b2ee9a53fab09e29', 1,
        );
    }

    private function operationsHealthPrerequisites(): PrerequisiteExecutionData
    {
        return new PrerequisiteExecutionData(
            null,
            'dk', 'notification-delivery', 'admin.delivery',
            'nd-component-operations-health', 'v.b2ee9a53fab09e29', 1,
            secretReferences: ['credential' => LocalLoopbackSecretHandleResolver::HANDLE],
        );
    }
}
