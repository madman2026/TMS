<?php

namespace Tests\Unit;

use App\Acceptance\Prerequisites\Data\PrerequisiteExecutionData;
use App\Acceptance\Targets\Contracts\TargetAccountResolver;
use App\Acceptance\Targets\Contracts\TargetCleanup;
use App\Acceptance\Targets\Contracts\TargetFixtureManager;
use App\Acceptance\Targets\Contracts\TargetOracle;
use App\Acceptance\Targets\Contracts\TargetReadinessProbe;
use App\Acceptance\Targets\Data\CleanupResult;
use App\Acceptance\Targets\Data\ResourceProvisionResult;
use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetContext;
use App\Acceptance\Targets\Data\TargetEnvironment;
use App\Acceptance\Targets\Data\TargetExecutionOutcome;
use App\Acceptance\Targets\Data\TargetOracleResult;
use App\Acceptance\Targets\Data\TargetReadinessResult;
use App\Acceptance\Targets\TargetResourceAdapters;
use App\Acceptance\Targets\TargetResourceCoordinator;
use App\Acceptance\Targets\TargetResourceRegistry;
use Closure;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TargetResourceCoordinatorTest extends TestCase
{
    public function test_it_runs_the_complete_lifecycle_in_order_and_passes_the_cleanup_budget(): void
    {
        $events = [];
        $account = new ResourceReference('account', 'opaque-account');
        $fixture = new ResourceReference('fixture', 'opaque-fixture');
        $adapters = new ConfigurableTargetAdapters(
            readiness: function () use (&$events): TargetReadinessResult {
                $events[] = 'readiness';

                return TargetReadinessResult::ready(TargetEnvironment::TESTING);
            },
            accounts: function () use (&$events, $account): ResourceProvisionResult {
                $events[] = 'account';

                return ResourceProvisionResult::success([$account]);
            },
            fixtures: function (TargetContext $context) use (&$events, $fixture): ResourceProvisionResult {
                $events[] = 'fixture';
                $this->assertCount(1, $context->references);

                return ResourceProvisionResult::success([$fixture]);
            },
            oracle: function (TargetContext $context) use (&$events): TargetOracleResult {
                $events[] = 'oracle';
                $this->assertCount(2, $context->references);

                return TargetOracleResult::passed();
            },
            cleanup: function (TargetContext $context, int $timeoutMs) use (&$events): CleanupResult {
                $events[] = 'cleanup';
                $this->assertSame(TargetResourceCoordinator::CLEANUP_TIMEOUT_MS, $timeoutMs);

                return CleanupResult::success($context->references);
            },
        );

        $result = $this->coordinator($adapters)->execute(
            $this->context(),
            $this->prerequisites(),
            function (TargetContext $context) use (&$events): TargetExecutionOutcome {
                $events[] = 'execution';
                $this->assertCount(2, $context->references);

                return TargetExecutionOutcome::succeeded();
            },
        );

        $this->assertSame(['readiness', 'account', 'fixture', 'execution', 'oracle', 'cleanup'], $events);
        $this->assertSame('succeeded', $result->status);
        $this->assertSame('complete', $result->stage);
        $this->assertNull($result->primaryErrorCode);
        $this->assertTrue($result->cleanup?->succeeded);
    }

    public function test_missing_adapter_and_unsafe_environment_fail_closed_before_execution(): void
    {
        $executed = false;
        $missing = (new TargetResourceCoordinator(new TargetResourceRegistry))->execute(
            $this->context(),
            $this->prerequisites(),
            function () use (&$executed): TargetExecutionOutcome {
                $executed = true;

                return TargetExecutionOutcome::succeeded();
            },
        );
        $this->assertSame('resource_unavailable', $missing->primaryErrorCode);
        $this->assertFalse($executed);

        $unsafeAdapters = $this->adapters(
            readiness: fn (): TargetReadinessResult => TargetReadinessResult::unsafe(TargetEnvironment::PRODUCTION),
        );
        $unsafe = $this->coordinator($unsafeAdapters)->execute(
            $this->context(),
            $this->prerequisites(),
            fn (): TargetExecutionOutcome => TargetExecutionOutcome::succeeded(),
        );
        $this->assertSame('unsafe_target', $unsafe->primaryErrorCode);
        $this->assertSame('readiness', $unsafe->stage);
    }

    public function test_partial_fixture_failure_is_cleaned_and_never_reaches_execution_or_oracle(): void
    {
        $account = new ResourceReference('account', 'account-a');
        $partial = new ResourceReference('fixture', 'partial-a');
        $executed = false;
        $oracleCalled = false;
        $cleaned = [];
        $adapters = $this->adapters(
            accounts: fn (): ResourceProvisionResult => ResourceProvisionResult::success([$account]),
            fixtures: fn (): ResourceProvisionResult => ResourceProvisionResult::fixtureFailed([$partial]),
            oracle: function () use (&$oracleCalled): TargetOracleResult {
                $oracleCalled = true;

                return TargetOracleResult::passed();
            },
            cleanup: function (TargetContext $context) use (&$cleaned): CleanupResult {
                $cleaned = $context->references;

                return CleanupResult::success($context->references);
            },
        );

        $result = $this->coordinator($adapters)->execute(
            $this->context(),
            $this->prerequisites(),
            function () use (&$executed): TargetExecutionOutcome {
                $executed = true;

                return TargetExecutionOutcome::succeeded();
            },
        );

        $this->assertSame('fixture_setup_failed', $result->primaryErrorCode);
        $this->assertCount(2, $cleaned);
        $this->assertFalse($executed);
        $this->assertFalse($oracleCalled);
    }

    public function test_primary_execution_failure_wins_over_oracle_and_cleanup_failures(): void
    {
        $reference = new ResourceReference('account', 'account-a');
        $oracleCalled = false;
        $adapters = $this->adapters(
            accounts: fn (): ResourceProvisionResult => ResourceProvisionResult::success([$reference]),
            oracle: function () use (&$oracleCalled): TargetOracleResult {
                $oracleCalled = true;

                return TargetOracleResult::failed();
            },
            cleanup: fn (TargetContext $context): CleanupResult => CleanupResult::failed([], $context->references, true),
        );

        $result = $this->coordinator($adapters)->execute(
            $this->context(),
            $this->prerequisites(),
            fn (): TargetExecutionOutcome => TargetExecutionOutcome::failed(
                'acceptance_step_failed', true, false, true, false,
            ),
        );

        $this->assertTrue($oracleCalled);
        $this->assertSame('acceptance_step_failed', $result->primaryErrorCode);
        $this->assertSame('cleanup_failed', $result->cleanupErrorCode);
        $this->assertSame('execution', $result->stage);
    }

    public function test_normalized_execution_exception_skips_oracle_but_still_cleans_up(): void
    {
        $reference = new ResourceReference('account', 'account-a');
        $oracleCalled = false;
        $cleanupCalled = false;
        $adapters = $this->adapters(
            accounts: fn (): ResourceProvisionResult => ResourceProvisionResult::success([$reference]),
            oracle: function () use (&$oracleCalled): TargetOracleResult {
                $oracleCalled = true;

                return TargetOracleResult::passed();
            },
            cleanup: function (TargetContext $context) use (&$cleanupCalled): CleanupResult {
                $cleanupCalled = true;

                return CleanupResult::success($context->references);
            },
        );

        $result = $this->coordinator($adapters)->execute(
            $this->context(),
            $this->prerequisites(),
            fn (): TargetExecutionOutcome => throw new RuntimeException('inert-sensitive-value'),
        );

        $this->assertSame('acceptance_command_failed', $result->primaryErrorCode);
        $this->assertFalse($oracleCalled);
        $this->assertTrue($cleanupCalled);
    }

    public function test_cancellation_is_checked_at_every_boundary_and_reference_paths_still_clean_up(): void
    {
        foreach (range(1, 5) as $cancelAt) {
            $events = [];
            $checks = 0;
            $reference = new ResourceReference('account', 'account-'.$cancelAt);
            $adapters = $this->adapters(
                readiness: function () use (&$events): TargetReadinessResult {
                    $events[] = 'readiness';

                    return TargetReadinessResult::ready(TargetEnvironment::TESTING);
                },
                accounts: function () use (&$events, $reference): ResourceProvisionResult {
                    $events[] = 'account';

                    return ResourceProvisionResult::success([$reference]);
                },
                fixtures: function () use (&$events): ResourceProvisionResult {
                    $events[] = 'fixture';

                    return ResourceProvisionResult::success([]);
                },
                oracle: function () use (&$events): TargetOracleResult {
                    $events[] = 'oracle';

                    return TargetOracleResult::passed();
                },
                cleanup: function (TargetContext $context) use (&$events): CleanupResult {
                    $events[] = 'cleanup';

                    return CleanupResult::success($context->references);
                },
            );

            $result = $this->coordinator($adapters)->execute(
                $this->context(),
                $this->prerequisites(),
                function () use (&$events): TargetExecutionOutcome {
                    $events[] = 'execution';

                    return TargetExecutionOutcome::succeeded();
                },
                function () use (&$checks, $cancelAt): bool {
                    $checks++;

                    return $checks === $cancelAt;
                },
            );

            $this->assertSame('cancelled', $result->status);
            $this->assertSame('cancelled', $result->stage);
            $this->assertNotContains('oracle', $events);
            $cancelAt >= 3
                ? $this->assertContains('cleanup', $events)
                : $this->assertNotContains('cleanup', $events);
        }
    }

    public function test_each_adapter_exception_is_normalized_to_its_stage_code(): void
    {
        $notReady = $this->coordinator($this->adapters(
            readiness: fn (): TargetReadinessResult => TargetReadinessResult::notReady(TargetEnvironment::STAGING),
        ))->execute($this->context(), $this->prerequisites(), fn () => TargetExecutionOutcome::succeeded());
        $this->assertSame('target_not_ready', $notReady->primaryErrorCode);

        $readinessException = $this->coordinator($this->adapters(
            readiness: fn (): TargetReadinessResult => throw new RuntimeException('inert-provider-error'),
        ))->execute($this->context(), $this->prerequisites(), fn () => TargetExecutionOutcome::succeeded());
        $this->assertSame('unsafe_target', $readinessException->primaryErrorCode);

        $accountException = $this->coordinator($this->adapters(
            accounts: fn (): ResourceProvisionResult => throw new RuntimeException('inert-provider-error'),
        ))->execute($this->context(), $this->prerequisites(), fn () => TargetExecutionOutcome::succeeded());
        $this->assertSame('resource_unavailable', $accountException->primaryErrorCode);
        $this->assertNull($accountException->cleanup);

        $fixtureException = $this->coordinator($this->adapters(
            fixtures: fn (): ResourceProvisionResult => throw new RuntimeException('inert-provider-error'),
        ))->execute($this->context(), $this->prerequisites(), fn () => TargetExecutionOutcome::succeeded());
        $this->assertSame('fixture_setup_failed', $fixtureException->primaryErrorCode);
        $this->assertTrue($fixtureException->cleanup?->succeeded);

        $oracleException = $this->coordinator($this->adapters(
            oracle: fn (): TargetOracleResult => throw new RuntimeException('inert-provider-error'),
        ))->execute($this->context(), $this->prerequisites(), fn () => TargetExecutionOutcome::succeeded());
        $this->assertSame('oracle_failed', $oracleException->primaryErrorCode);

        $cleanupException = $this->coordinator($this->adapters(
            cleanup: fn (): CleanupResult => throw new RuntimeException('inert-provider-error'),
        ))->execute($this->context(), $this->prerequisites(), fn () => TargetExecutionOutcome::succeeded());
        $this->assertNull($cleanupException->primaryErrorCode);
        $this->assertSame('cleanup_failed', $cleanupException->cleanupErrorCode);
        $this->assertSame('cleanup', $cleanupException->stage);
    }

    public function test_repeated_lifecycle_identity_reaches_idempotent_cleanup_without_reordering(): void
    {
        $seenLifecycleIds = [];
        $adapter = $this->adapters(
            cleanup: function (TargetContext $context) use (&$seenLifecycleIds): CleanupResult {
                $seenLifecycleIds[] = $context->lifecycleId;

                return CleanupResult::success($context->references);
            },
        );
        $coordinator = $this->coordinator($adapter);

        $first = $coordinator->execute(
            $this->context(), $this->prerequisites(), fn () => TargetExecutionOutcome::succeeded(),
        );
        $second = $coordinator->execute(
            $this->context(), $this->prerequisites(), fn () => TargetExecutionOutcome::succeeded(),
        );

        $this->assertSame([$first->lifecycleId, $first->lifecycleId], $seenLifecycleIds);
        $this->assertSame(
            array_map(fn (ResourceReference $reference): string => $reference->referenceHash, $first->references),
            array_map(fn (ResourceReference $reference): string => $reference->referenceHash, $second->references),
        );
        $this->assertEquals($first->cleanup, $second->cleanup);
    }

    private function coordinator(ConfigurableTargetAdapters $adapter): TargetResourceCoordinator
    {
        $registry = new TargetResourceRegistry;
        $registry->register('app-a', new TargetResourceAdapters($adapter, $adapter, $adapter, $adapter, $adapter));

        return new TargetResourceCoordinator($registry);
    }

    private function adapters(
        ?Closure $readiness = null,
        ?Closure $accounts = null,
        ?Closure $fixtures = null,
        ?Closure $oracle = null,
        ?Closure $cleanup = null,
    ): ConfigurableTargetAdapters {
        return new ConfigurableTargetAdapters(
            $readiness ?? fn (): TargetReadinessResult => TargetReadinessResult::ready(TargetEnvironment::TESTING),
            $accounts ?? fn (): ResourceProvisionResult => ResourceProvisionResult::success([
                new ResourceReference('account', 'account-a'),
            ]),
            $fixtures ?? fn (): ResourceProvisionResult => ResourceProvisionResult::success([]),
            $oracle ?? fn (): TargetOracleResult => TargetOracleResult::passed(),
            $cleanup ?? fn (TargetContext $context): CleanupResult => CleanupResult::success($context->references),
        );
    }

    private function context(): TargetContext
    {
        return new TargetContext(
            'dcb1cf9d-207c-4a44-963b-000000000001',
            'dcb1cf9d-207c-4a44-963b-000000000002',
            null,
            'app-a', 'component-a', 'suite-a', 'scenario-a', 'variant-a', 1,
        );
    }

    private function prerequisites(): PrerequisiteExecutionData
    {
        return new PrerequisiteExecutionData(
            null, 'app-a', 'component-a', 'suite-a', 'scenario-a', 'variant-a', 1,
        );
    }
}

final class ConfigurableTargetAdapters implements TargetAccountResolver, TargetCleanup, TargetFixtureManager, TargetOracle, TargetReadinessProbe
{
    public function __construct(
        private readonly Closure $readiness,
        private readonly Closure $accounts,
        private readonly Closure $fixtures,
        private readonly Closure $oracle,
        private readonly Closure $cleanup,
    ) {}

    public function probe(TargetContext $context): TargetReadinessResult
    {
        return ($this->readiness)($context);
    }

    public function resolve(TargetContext $context, PrerequisiteExecutionData $prerequisites): ResourceProvisionResult
    {
        return ($this->accounts)($context, $prerequisites);
    }

    public function provision(TargetContext $context, PrerequisiteExecutionData $prerequisites): ResourceProvisionResult
    {
        return ($this->fixtures)($context, $prerequisites);
    }

    public function evaluate(TargetContext $context, TargetExecutionOutcome $execution): TargetOracleResult
    {
        return ($this->oracle)($context, $execution);
    }

    public function cleanup(TargetContext $context, int $timeoutMs): CleanupResult
    {
        return ($this->cleanup)($context, $timeoutMs);
    }
}
