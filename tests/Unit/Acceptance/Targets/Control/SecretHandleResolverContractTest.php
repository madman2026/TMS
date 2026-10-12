<?php

namespace Tests\Unit\Acceptance\Targets\Control;

use App\Acceptance\Targets\Control\Contracts\SecretHandleResolver;
use App\Acceptance\Targets\Control\Data\TargetControlCredential;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class SecretHandleResolverContractTest extends TestCase
{
    public function test_resolver_contract_returns_a_credential_without_exposing_a_value_property(): void
    {
        $method = new ReflectionMethod(SecretHandleResolver::class, 'resolve');
        $credential = new ReflectionClass(TargetControlCredential::class);

        $this->assertSame(TargetControlCredential::class, (string) $method->getReturnType());
        $this->assertTrue($credential->isFinal());
        $this->assertTrue($credential->isReadOnly());
        $this->assertSame([], array_map(
            static fn (\ReflectionProperty $property): string => $property->getName(),
            $credential->getProperties(\ReflectionProperty::IS_PUBLIC),
        ));
    }

    public function test_credential_value_is_available_only_inside_the_callback(): void
    {
        $credential = new TargetControlCredential('inert-sensitive-sentinel');
        $seen = $credential->withValue(static fn (string $value): string => hash('sha256', $value));

        $this->assertSame(hash('sha256', 'inert-sensitive-sentinel'), $seen);
        $this->assertStringNotContainsString('inert-sensitive-sentinel', print_r($credential, true));
        $this->assertStringNotContainsString('inert-sensitive-sentinel', json_encode($credential, JSON_THROW_ON_ERROR));
    }

    public function test_credential_rejects_string_conversion(): void
    {
        $credential = new TargetControlCredential('inert-sensitive-sentinel');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('target_control_credential_non_stringable');

        (string) $credential;
    }

    public function test_credential_rejects_php_serialization(): void
    {
        $credential = new TargetControlCredential('inert-sensitive-sentinel');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('target_control_credential_non_serializable');

        serialize($credential);
    }
}
