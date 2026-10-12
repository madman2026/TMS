<?php

namespace Tests\Unit\Acceptance\Targets\Control;

use App\Acceptance\Targets\Control\Contracts\TargetControlClient;
use App\Acceptance\Targets\Control\Data\TargetControlCapabilities;
use App\Acceptance\Targets\Control\Data\TargetControlLeaseRequest;
use App\Acceptance\Targets\Control\Data\TargetControlLeaseResult;
use App\Acceptance\Targets\Control\Exceptions\TargetControlException;
use App\Acceptance\Targets\Control\Http\HttpTargetControlClient;
use App\Acceptance\Targets\Control\TargetControlProtocol;
use App\Acceptance\Targets\Data\ResourceReference;
use InvalidArgumentException;
use JsonSerializable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class TargetControlDataTest extends TestCase
{
    public function test_request_has_an_exact_safe_payload_and_deterministic_digest(): void
    {
        $request = $this->request();

        $this->assertSame(
            '93737a32794fc01f0eaa68f73314f0f51d49b95a66d1f5fd4434a18b8ddaa303',
            $request->requestDigest,
        );
        $this->assertSame([
            'protocol' => 'acceptance-control.v1',
            'lifecycle_id' => 'dcb1cf9d-207c-4a44-963b-000000000001',
            'correlation_id' => 'dcb1cf9d-207c-4a44-963b-000000000002',
            'app' => 'app-a',
            'component' => 'component-a',
            'recipe' => 'recipe-a.v1',
            'fixed_instant' => '2030-01-02T03:04:05Z',
            'lease_ttl_seconds' => 300,
            'request_digest' => $request->requestDigest,
        ], $request->payload());
    }

    #[DataProvider('invalidRequestProvider')]
    public function test_request_rejects_invalid_identity_time_token_and_ttl(array $override): void
    {
        $values = [
            'lifecycleId' => 'dcb1cf9d-207c-4a44-963b-000000000001',
            'correlationId' => 'dcb1cf9d-207c-4a44-963b-000000000002',
            'appKey' => 'app-a',
            'componentKey' => 'component-a',
            'recipeKey' => 'recipe-a.v1',
            'fixedInstant' => '2030-01-02T03:04:05Z',
            'leaseTtlSeconds' => 300,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('target_control_lease_request_invalid');

        new TargetControlLeaseRequest(...array_replace($values, $override));
    }

    public static function invalidRequestProvider(): iterable
    {
        yield 'invalid lifecycle' => [['lifecycleId' => 'not-a-uuid']];
        yield 'unsafe app' => [['appKey' => 'App A']];
        yield 'unsafe recipe' => [['recipeKey' => 'recipe/a']];
        yield 'non utc instant' => [['fixedInstant' => '2030-01-02T03:04:05+00:00']];
        yield 'normalized but invalid date' => [['fixedInstant' => '2030-02-30T03:04:05Z']];
        yield 'zero ttl' => [['leaseTtlSeconds' => 0]];
        yield 'excessive ttl' => [['leaseTtlSeconds' => TargetControlProtocol::MAX_LEASE_TTL_SECONDS + 1]];
    }

    public function test_capabilities_preserve_component_boundaries_and_exact_support(): void
    {
        $capabilities = new TargetControlCapabilities(
            TargetControlProtocol::IDENTITY,
            'server-v1',
            600,
            ['component-a' => ['recipe-a.v1', 'recipe-b.v1']],
            ['component-a' => ['observation-a.v1']],
        );

        $this->assertTrue($capabilities->supports('component-a', 'recipe-a.v1'));
        $this->assertFalse($capabilities->supports('component-a', 'recipe-c.v1'));
        $this->assertFalse($capabilities->supports('component-b', 'recipe-a.v1'));
        $this->assertSame(['observation-a.v1'], $capabilities->observationsByComponent['component-a']);
    }

    public function test_lease_result_accepts_only_bounded_opaque_references_and_semantic_facts(): void
    {
        $request = $this->request();
        $reference = new ResourceReference('fixture', 'opaque-fixture-a');
        $leaseContext = new ResourceReference('lease-context', 'opaque-context-a');
        $facts = [
            'available' => true,
            'count' => 2,
            'ratio' => 0.25,
            'state' => 'ready',
            'reason' => null,
        ];

        $result = new TargetControlLeaseResult(
            TargetControlProtocol::IDENTITY,
            $request->lifecycleId,
            $request->requestDigest,
            'active',
            [$reference],
            $leaseContext,
            'observation-a.v1',
            $facts,
        );

        $this->assertSame([$reference], $result->references);
        $this->assertSame($leaseContext, $result->leaseContextReference);
        $this->assertSame($facts, $result->facts);
    }

    public function test_exception_classification_is_closed_and_safe(): void
    {
        $unavailable = TargetControlException::because('target_control_unavailable', 503);
        $conflict = TargetControlException::because('target_control_lease_conflict', 409);

        $this->assertTrue($unavailable->retryable);
        $this->assertFalse($unavailable->permanent);
        $this->assertFalse($conflict->retryable);
        $this->assertTrue($conflict->permanent);
        $this->assertSame('target_control_lease_conflict', $conflict->getMessage());

        $this->expectException(InvalidArgumentException::class);
        TargetControlException::because('unknown_remote_error');
    }

    public function test_data_contracts_are_final_readonly_and_not_json_serializable(): void
    {
        foreach ([
            TargetControlCapabilities::class,
            TargetControlLeaseRequest::class,
            TargetControlLeaseResult::class,
        ] as $class) {
            $reflection = new ReflectionClass($class);

            $this->assertTrue($reflection->isFinal());
            $this->assertTrue($reflection->isReadOnly());
            $this->assertFalse($reflection->implementsInterface(JsonSerializable::class));
            $this->assertFalse($reflection->hasMethod('toArray'));
        }

        $client = new ReflectionClass(HttpTargetControlClient::class);
        $this->assertTrue($client->implementsInterface(TargetControlClient::class));
    }

    private function request(): TargetControlLeaseRequest
    {
        return new TargetControlLeaseRequest(
            'dcb1cf9d-207c-4a44-963b-000000000001',
            'dcb1cf9d-207c-4a44-963b-000000000002',
            'app-a',
            'component-a',
            'recipe-a.v1',
            '2030-01-02T03:04:05Z',
            300,
        );
    }
}
