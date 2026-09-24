<?php

namespace Tests\Unit;

use App\Exceptions\AcceptanceRegistryException;
use App\Services\AcceptanceAppRegistry;
use Modules\Core\Contracts\AcceptanceApp;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\TestContext;
use PHPUnit\Framework\TestCase;

class AcceptanceAppRegistryTest extends TestCase
{
    public function test_it_registers_and_resolves_apps_and_scenarios_in_order(): void
    {
        $firstScenario = $this->scenario('first-scenario');
        $secondScenario = $this->scenario('second-scenario');
        $firstApp = $this->app('first-app', [$firstScenario, $secondScenario]);
        $secondApp = $this->app('second-app');
        $registry = new AcceptanceAppRegistry;

        $registry->register($firstApp);
        $registry->register($secondApp);

        $this->assertSame($firstApp, $registry->app('first-app'));
        $this->assertSame($firstScenario, $registry->scenario('first-app', 'first-scenario'));
        $this->assertNull($registry->app('missing-app'));
        $this->assertNull($registry->scenario('first-app', 'missing-scenario'));
        $this->assertSame(['first-app', 'second-app'], $registry->appKeys());
        $this->assertSame(['first-scenario', 'second-scenario'], $registry->scenarioKeys('first-app'));
    }

    public function test_it_rejects_a_duplicate_app_key_without_replacing_the_original(): void
    {
        $original = $this->app('local-app');
        $registry = new AcceptanceAppRegistry;
        $registry->register($original);

        try {
            $registry->register($this->app('local-app'));
            $this->fail('Expected an AcceptanceRegistryException.');
        } catch (AcceptanceRegistryException $exception) {
            $this->assertSame('acceptance_registry_duplicate', $exception->errorCode);
        }

        $this->assertSame($original, $registry->app('local-app'));
    }

    public function test_it_rejects_duplicate_scenario_keys_without_registering_the_app(): void
    {
        $registry = new AcceptanceAppRegistry;

        try {
            $registry->register($this->app('local-app', [
                $this->scenario('local-scenario'),
                $this->scenario('local-scenario'),
            ]));
            $this->fail('Expected an AcceptanceRegistryException.');
        } catch (AcceptanceRegistryException $exception) {
            $this->assertSame('acceptance_registry_duplicate', $exception->errorCode);
        }

        $this->assertNull($registry->app('local-app'));
    }

    public function test_it_rejects_invalid_keys_and_scenario_values_safely(): void
    {
        foreach ([
            $this->app('Invalid App'),
            $this->app('local-app', [$this->scenario('Invalid Scenario')]),
            $this->app('local-app', [new \stdClass]),
        ] as $invalidApp) {
            $registry = new AcceptanceAppRegistry;

            try {
                $registry->register($invalidApp);
                $this->fail('Expected an AcceptanceRegistryException.');
            } catch (AcceptanceRegistryException $exception) {
                $this->assertSame('acceptance_registry_invalid', $exception->errorCode);
                $this->assertSame(
                    'The Acceptance Registry definition is invalid.',
                    $exception->getMessage(),
                );
            }
        }
    }

    /**
     * @param  array<int, mixed>  $scenarios
     */
    private function app(string $key, array $scenarios = []): AcceptanceApp
    {
        return new class($key, $scenarios) implements AcceptanceApp
        {
            /**
             * @param  array<int, mixed>  $scenarios
             */
            public function __construct(
                private readonly string $key,
                private readonly array $scenarios,
            ) {}

            public function key(): string
            {
                return $this->key;
            }

            public function scenarios(): iterable
            {
                yield from $this->scenarios;
            }
        };
    }

    private function scenario(string $key): AcceptanceScenario
    {
        return new class($key) implements AcceptanceScenario
        {
            public function __construct(private readonly string $key) {}

            public function key(): string
            {
                return $this->key;
            }

            public function name(): string
            {
                return 'Local scenario';
            }

            public function steps(TestContext $context): iterable
            {
                return [];
            }
        };
    }
}
