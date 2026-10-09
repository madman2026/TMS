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
use App\Acceptance\Targets\Data\TargetResourceLifecycleData;
use App\Acceptance\Targets\TargetResourceAdapters;
use App\Acceptance\Targets\TargetResourceRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TargetResourceContractSafetyTest extends TestCase
{
    public function test_reference_has_deterministic_hash_and_arrays_are_defensively_copied_and_deduplicated(): void
    {
        $reference = new ResourceReference('account', 'opaque-a');
        $same = new ResourceReference('account', 'opaque-a');
        $references = [$reference];
        $context = $this->context($references);
        $references[] = new ResourceReference('fixture', 'opaque-b');

        $this->assertSame(hash('sha256', "account\0opaque-a"), $reference->referenceHash);
        $this->assertSame($reference->referenceHash, $same->referenceHash);
        $this->assertCount(1, $context->references);
        $this->assertCount(1, $context->withReferences([$same])->references);

        $this->expectException(InvalidArgumentException::class);
        ResourceProvisionResult::success([$reference, $same]);
    }

    #[DataProvider('unsafeReferenceProvider')]
    public function test_reference_rejects_secret_personal_url_and_non_opaque_sentinels(string $id): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('resource_reference_invalid');

        new ResourceReference('account', $id);
    }

    public static function unsafeReferenceProvider(): array
    {
        return [
            'url' => ['https://target.test/resource'],
            'email' => ['person@example.test'],
            'secret' => ['example-sensitive-value'],
            'credential' => ['credential-a'],
            'token' => ['token-a'],
            'selector' => ['selector-a'],
            'whitespace' => ['opaque value'],
            'separator' => ['opaque/value'],
        ];
    }

    public function test_readiness_never_allows_production_or_unknown_to_be_ready(): void
    {
        foreach ([TargetEnvironment::PRODUCTION, TargetEnvironment::UNKNOWN] as $environment) {
            try {
                TargetReadinessResult::ready($environment);
                $this->fail('Unsafe environments must not be ready.');
            } catch (InvalidArgumentException $exception) {
                $this->assertSame('target_readiness_result_invalid', $exception->getMessage());
            }
            $this->assertSame('unsafe_target', TargetReadinessResult::unsafe($environment)->errorCode);
        }
    }

    public function test_dtos_reject_inconsistent_status_code_and_cleanup_combinations(): void
    {
        $reference = new ResourceReference('account', 'opaque-a');
        $invalid = [
            fn () => new TargetExecutionOutcome('succeeded', 'acceptance_command_failed', true, null, null, false),
            fn () => new TargetOracleResult(true, 'oracle_failed', false, true, false),
            fn () => new CleanupResult(true, [], [$reference], false, null, null, null, false),
            fn () => new TargetResourceLifecycleData(
                'dcb1cf9d-207c-4a44-963b-000000000001',
                'dcb1cf9d-207c-4a44-963b-000000000002',
                null,
                'app-a', 'component-a', 'suite-a', 'scenario-a', 'variant-a', 1,
                'succeeded', 'complete', null, [], null, null, null, 'oracle_failed', null,
            ),
        ];

        foreach ($invalid as $factory) {
            try {
                $factory();
                $this->fail('Expected an invalid target resource DTO.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_registry_rejects_invalid_and_duplicate_app_keys(): void
    {
        $adapter = new class implements TargetAccountResolver, TargetCleanup, TargetFixtureManager, TargetOracle, TargetReadinessProbe
        {
            public function probe(TargetContext $context): TargetReadinessResult
            {
                return TargetReadinessResult::ready(TargetEnvironment::TESTING);
            }

            public function resolve(TargetContext $context, PrerequisiteExecutionData $prerequisites): ResourceProvisionResult
            {
                return ResourceProvisionResult::success([new ResourceReference('account', 'opaque-a')]);
            }

            public function provision(TargetContext $context, PrerequisiteExecutionData $prerequisites): ResourceProvisionResult
            {
                return ResourceProvisionResult::success([]);
            }

            public function evaluate(TargetContext $context, TargetExecutionOutcome $execution): TargetOracleResult
            {
                return TargetOracleResult::passed();
            }

            public function cleanup(TargetContext $context, int $timeoutMs): CleanupResult
            {
                return CleanupResult::success($context->references);
            }
        };
        $aggregate = new TargetResourceAdapters($adapter, $adapter, $adapter, $adapter, $adapter);
        $registry = new TargetResourceRegistry;
        $registry->register('app-a', $aggregate);
        $this->assertSame($aggregate, $registry->for('app-a'));

        foreach (['app-a', 'Invalid App'] as $key) {
            try {
                $registry->register($key, $aggregate);
                $this->fail('Expected registry validation failure.');
            } catch (InvalidArgumentException $exception) {
                $this->assertContains($exception->getMessage(), [
                    'target_resource_registry_duplicate', 'target_resource_registry_invalid',
                ]);
            }
        }
    }

    public function test_internal_prerequisite_data_is_copied_ordered_and_not_serializable(): void
    {
        $values = ['z' => true, 'a' => ['one']];
        $references = ['credential' => 'app-secret://app-a/opaque-a'];
        $data = new PrerequisiteExecutionData(
            null, 'app-a', 'component-a', 'suite-a', 'scenario-a', 'variant-a', 1, $values, $references,
        );
        $values['a'][] = 'two';
        $references['credential'] = 'app-secret://app-a/changed';

        $this->assertSame(['a', 'z'], array_keys($data->nonSensitiveInputs));
        $this->assertSame(['one'], $data->nonSensitiveInputs['a']);
        $this->assertSame('app-secret://app-a/opaque-a', $data->secretReferences['credential']);

        foreach ([
            PrerequisiteExecutionData::class,
            ResourceReference::class,
            TargetContext::class,
            TargetReadinessResult::class,
            ResourceProvisionResult::class,
            TargetExecutionOutcome::class,
            TargetOracleResult::class,
            CleanupResult::class,
            TargetResourceLifecycleData::class,
            TargetResourceAdapters::class,
        ] as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue($reflection->isFinal());
            $this->assertTrue($reflection->isReadOnly());
            $this->assertFalse($reflection->implementsInterface(\JsonSerializable::class));
            $this->assertFalse($reflection->hasMethod('toArray'));
        }
    }

    /** @param list<ResourceReference> $references */
    private function context(array $references): TargetContext
    {
        return new TargetContext(
            'dcb1cf9d-207c-4a44-963b-000000000001',
            'dcb1cf9d-207c-4a44-963b-000000000002',
            null,
            'app-a', 'component-a', 'suite-a', 'scenario-a', 'variant-a', 1, $references,
        );
    }
}
