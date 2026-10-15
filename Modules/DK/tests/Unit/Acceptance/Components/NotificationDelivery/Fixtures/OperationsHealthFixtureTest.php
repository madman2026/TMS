<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit\Acceptance\Components\NotificationDelivery\Fixtures;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Control\Data\TargetControlLeaseResult;
use App\Acceptance\Targets\Control\TargetControlProtocol;
use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetContext;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\OperationsHealthVariant;
use Modules\DK\Acceptance\Components\NotificationDelivery\Fixtures\OperationsHealthFixture;
use Modules\DK\Acceptance\Shared\Target\Contracts\DKSyntheticTargetControl;
use Modules\DK\Acceptance\Shared\Target\LocalLoopbackSecretHandleResolver;
use PHPUnit\Framework\TestCase;

final class OperationsHealthFixtureTest extends TestCase
{
    public function test_it_acquires_and_cleans_only_the_matching_run_owned_lease(): void
    {
        $calls = [];
        $control = $this->control($calls);
        $fixture = $this->fixture($control);
        $context = $this->context();

        $provisioned = $fixture->provision($context, $this->prerequisites());
        $finalContext = $context->withReferences([
            new ResourceReference('account', 'account-a'),
            ...$provisioned->references,
        ]);
        $cleaned = $fixture->cleanup($finalContext, 30_000);

        self::assertTrue($provisioned->succeeded);
        self::assertSame(['fixture', 'acceptance-lease-context'], array_column($provisioned->references, 'type'));
        self::assertTrue($cleaned->succeeded);
        self::assertSame($finalContext->references, $cleaned->cleanedReferences);
        self::assertSame([
            ['acquire', $context->lifecycleId, 'operations-health.fresh.v1'],
            ['cleanup', $context->lifecycleId, 'operations-health.fresh.v1'],
        ], $calls);
    }

    public function test_partial_references_are_preserved_when_acquisition_does_not_become_active(): void
    {
        $partial = new ResourceReference('fixture', 'partial-a');
        $control = new class($partial) implements DKSyntheticTargetControl
        {
            public function __construct(private ResourceReference $partial) {}

            public function acquire(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle): TargetControlLeaseResult
            {
                return new TargetControlLeaseResult(
                    TargetControlProtocol::IDENTITY,
                    $context->lifecycleId,
                    str_repeat('a', 64),
                    'expired',
                    [$this->partial],
                );
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

        $result = $this->fixture($control)->provision($this->context(), $this->prerequisites());

        self::assertFalse($result->succeeded);
        self::assertSame('fixture_setup_failed', $result->errorCode);
        self::assertSame([$partial], $result->references);
    }

    public function test_a_mismatched_handle_fails_before_target_control(): void
    {
        $calls = [];
        $fixture = $this->fixture($this->control($calls));
        $prerequisites = new PrerequisiteExecutionData(
            null,
            'dk',
            'notification-delivery',
            'admin.delivery',
            'nd-component-operations-health',
            'v.b2ee9a53fab09e29',
            1,
            secretReferences: ['credential' => 'app-secret://dk/other'],
        );

        $result = $fixture->provision($this->context(), $prerequisites);

        self::assertFalse($result->succeeded);
        self::assertSame([], $result->references);
        self::assertSame([], $calls);
    }

    /** @param array<int, array{string, string, string}> $calls */
    private function control(array &$calls): DKSyntheticTargetControl
    {
        return new class($calls) implements DKSyntheticTargetControl
        {
            /** @param array<int, array{string, string, string}> $calls */
            public function __construct(private array &$calls) {}

            public function acquire(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle): TargetControlLeaseResult
            {
                $this->calls[] = ['acquire', $context->lifecycleId, $variant->recipeKey];

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
                $this->calls[] = ['cleanup', $context->lifecycleId, $variant->recipeKey];

                return new TargetControlLeaseResult(
                    TargetControlProtocol::IDENTITY,
                    $context->lifecycleId,
                    str_repeat('a', 64),
                    'cleaned',
                    [],
                );
            }
        };
    }

    private function fixture(DKSyntheticTargetControl $control): OperationsHealthFixture
    {
        return new OperationsHealthFixture(
            $control,
            new OperationsHealthVariantCatalog,
            LocalLoopbackSecretHandleResolver::HANDLE,
        );
    }

    private function context(): TargetContext
    {
        return new TargetContext(
            'dcb1cf9d-207c-4a44-963b-000000000001',
            'dcb1cf9d-207c-4a44-963b-000000000002',
            null,
            'dk',
            'notification-delivery',
            'admin.delivery',
            'nd-component-operations-health',
            'v.b2ee9a53fab09e29',
            1,
        );
    }

    private function prerequisites(): PrerequisiteExecutionData
    {
        return new PrerequisiteExecutionData(
            null,
            'dk',
            'notification-delivery',
            'admin.delivery',
            'nd-component-operations-health',
            'v.b2ee9a53fab09e29',
            1,
            secretReferences: ['credential' => LocalLoopbackSecretHandleResolver::HANDLE],
        );
    }
}
