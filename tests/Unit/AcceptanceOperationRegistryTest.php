<?php

namespace Tests\Unit;

use App\Acceptance\Operations\AcceptanceOperationRegistry;
use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use UnexpectedValueException;

class AcceptanceOperationRegistryTest extends TestCase
{
    public function test_registration_and_unknown_lookup_are_lazy_and_only_the_selected_factory_runs(): void
    {
        $registry = new AcceptanceOperationRegistry;
        $calls = ['list' => 0, 'run' => 0];
        $handler = $this->handler('acceptance.list');
        $registry->register('acceptance.list', function () use (&$calls, $handler) {
            $calls['list']++;

            return $handler;
        });
        $registry->register('acceptance.run', function () use (&$calls) {
            $calls['run']++;
            throw new RuntimeException('example-sensitive-value');
        });
        $this->assertTrue($registry->has('acceptance.list'));
        $this->assertFalse($registry->has('unknown'));
        $this->assertNull($registry->resolve('unknown'));
        $this->assertSame(['list' => 0, 'run' => 0], $calls);
        $this->assertSame($handler, $registry->resolve('acceptance.list'));
        $this->assertSame(['list' => 1, 'run' => 0], $calls);
    }

    public function test_invalid_and_duplicate_registration_have_fixed_safe_exceptions_without_running_factories(): void
    {
        $registry = new AcceptanceOperationRegistry;
        $factory = fn () => throw new RuntimeException('Factory must stay lazy.');
        foreach (['', 'EXAMPLE', 'example-sensitive-value/invalid', str_repeat('a', 65), "name\n"] as $name) {
            try {
                $registry->register($name, $factory);
                $this->fail('Expected invalid registration.');
            } catch (InvalidArgumentException $exception) {
                $this->assertSame('operation_registry_invalid', $exception->getMessage());
                $this->assertNull($exception->getPrevious());
            }
        }
        $registry->register('acceptance.list', $factory);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('operation_registry_duplicate');
        $registry->register('acceptance.list', $factory);
    }

    public function test_wrong_type_name_and_throwing_factories_discard_raw_details_and_exception_chains(): void
    {
        foreach ([fn () => new \stdClass, fn () => $this->handler('acceptance.plan'),
            fn () => throw new RuntimeException('example-sensitive-value')] as $factory) {
            $registry = new AcceptanceOperationRegistry;
            $registry->register('acceptance.list', $factory);
            try {
                $registry->resolve('acceptance.list');
                $this->fail('Expected normalized factory failure.');
            } catch (UnexpectedValueException $exception) {
                $this->assertSame('operation_registry_invalid', $exception->getMessage());
                $this->assertNull($exception->getPrevious());
            }
        }
    }

    public function test_target_module_operations_are_valid_registry_names(): void
    {
        $registry = new AcceptanceOperationRegistry;

        foreach (['acceptance.app.create', 'acceptance.app.validate', 'acceptance.component.create', 'acceptance.scenarios.import'] as $name) {
            $registry->register($name, fn () => $this->handler($name));
            $this->assertTrue($registry->has($name));
        }
    }

    private function handler(string $name): AcceptanceOperationHandler
    {
        return new class($name) implements AcceptanceOperationHandler
        {
            public function __construct(private string $operation) {}

            public function name(): string
            {
                return $this->operation;
            }

            public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
            {
                throw new RuntimeException('Registry lookup must not execute a handler.');
            }
        };
    }
}
