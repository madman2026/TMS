<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit\Acceptance\Shared\Target;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Control\Data\TargetControlLeaseResult;
use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetContext;
use LogicException;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\OperationsHealthVariant;
use Modules\DK\Acceptance\Shared\Target\Contracts\DKSyntheticTargetControl;
use Modules\DK\Acceptance\Shared\Target\DKBrowserTargetRuntime;
use Modules\DK\Acceptance\Shared\Target\DKBrowserTargetRuntimeResolver;
use Modules\DK\Acceptance\Shared\Target\LocalLoopbackSecretHandleResolver;
use PHPUnit\Framework\TestCase;

final class DKBrowserTargetRuntimeResolverTest extends TestCase
{
    public function test_it_creates_a_fresh_runtime_from_the_exact_final_context(): void
    {
        $resolver = $this->resolver();
        $context = $this->context();
        $prerequisites = $this->prerequisites();

        $first = $resolver->resolve($context, $prerequisites);
        $second = $resolver->resolve($context, $prerequisites);

        self::assertInstanceOf(DKBrowserTargetRuntime::class, $first);
        self::assertInstanceOf(DKBrowserTargetRuntime::class, $second);
        self::assertNotSame($first, $second);
    }

    public function test_missing_lease_context_or_mismatched_prerequisites_fail_closed(): void
    {
        $resolver = $this->resolver();
        $withoutLease = new TargetContext(
            'dcb1cf9d-207c-4a44-963b-000000000001',
            'dcb1cf9d-207c-4a44-963b-000000000002',
            null,
            'dk', 'notification-delivery', 'admin.delivery',
            'nd-component-operations-health', 'v.b2ee9a53fab09e29', 1,
        );

        try {
            $resolver->resolve($withoutLease, $this->prerequisites());
            self::fail('A lease-context reference is required.');
        } catch (AcceptanceExecutionException $exception) {
            self::assertSame('acceptance_configuration_invalid', $exception->errorCode);
        }

        $wrong = new PrerequisiteExecutionData(
            null,
            'dk', 'notification-delivery', 'admin.delivery',
            'nd-component-operations-health', 'v.b2ee9a53fab09e29', 1,
            secretReferences: ['credential' => 'app-secret://dk/other'],
        );
        $this->expectException(AcceptanceExecutionException::class);
        $resolver->resolve($this->context(), $wrong);
    }

    private function resolver(): DKBrowserTargetRuntimeResolver
    {
        $control = new class implements DKSyntheticTargetControl
        {
            public function acquire(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle): TargetControlLeaseResult
            {
                throw new LogicException('not called');
            }

            public function inspect(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle): TargetControlLeaseResult
            {
                throw new LogicException('not called');
            }

            public function cleanup(TargetContext $context, OperationsHealthVariant $variant, string $secretHandle, int $timeoutMs): TargetControlLeaseResult
            {
                throw new LogicException('not called');
            }
        };

        return new DKBrowserTargetRuntimeResolver(
            $control,
            new OperationsHealthVariantCatalog,
            'http://127.0.0.1:8000',
            LocalLoopbackSecretHandleResolver::HANDLE,
        );
    }

    private function context(): TargetContext
    {
        return new TargetContext(
            'dcb1cf9d-207c-4a44-963b-000000000001',
            'dcb1cf9d-207c-4a44-963b-000000000002',
            null,
            'dk', 'notification-delivery', 'admin.delivery',
            'nd-component-operations-health', 'v.b2ee9a53fab09e29', 1,
            [new ResourceReference('acceptance-lease-context', 'opaque-context-a')],
        );
    }

    private function prerequisites(): PrerequisiteExecutionData
    {
        return new PrerequisiteExecutionData(
            null,
            'dk', 'notification-delivery', 'admin.delivery',
            'nd-component-operations-health', 'v.b2ee9a53fab09e29', 1,
            secretReferences: ['credential' => LocalLoopbackSecretHandleResolver::HANDLE],
        );
    }
}
