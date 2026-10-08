<?php

namespace Tests\Feature;

use App\Acceptance\Operations\AcceptanceOperationRegistry;
use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Data\CatalogOperationData;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Operations\Data\RunOperationData;
use App\Contracts\AcceptanceComponentProvider;
use App\Data\ComponentDescriptor;
use App\Data\ScenarioDescriptor;
use App\Data\SuiteDescriptor;
use App\Data\VariantDescriptor;
use App\Models\Profile;
use App\Models\User;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceRunService;
use App\TestStatusEnum;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;
use Mockery;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\TestContext;
use Modules\Core\Data\AcceptanceExecutionIdentity;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Tests\TestCase;

class AcceptanceOperationCommandContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_operation_request_and_result_use_version_two_only(): void
    {
        $request = new OperationRequest('acceptance.list');
        $this->assertSame(2, $request->version);

        $this->expectException(InvalidArgumentException::class);
        new OperationResult(
            'acceptance.list',
            'succeeded',
            null,
            'dcb1cf9d-207c-4a44-963b-000000000009',
            version: 1,
        );
    }

    public function test_run_command_exposes_only_the_complete_hierarchy_signature(): void
    {
        /** @var Command $command */
        $command = Artisan::all()['acceptance:run'];
        $arguments = array_keys($command->getDefinition()->getArguments());

        $this->assertSame([
            'app', 'component', 'suite', 'scenario', 'variant', 'profile',
        ], $arguments);
        foreach (['app', 'component', 'suite', 'scenario', 'variant', 'profile'] as $name) {
            $this->assertTrue($command->getDefinition()->getArgument($name)->isRequired());
        }
    }

    public function test_old_partial_run_request_is_rejected_before_handler_resolution(): void
    {
        $calls = 0;
        $registry = new AcceptanceOperationRegistry;
        $registry->register('acceptance.run', function () use (&$calls) {
            $calls++;
            throw new \RuntimeException('must not resolve');
        });
        $service = new AcceptanceOperationService($registry);

        $result = $service->execute(new OperationRequest('acceptance.run', [
            'app_key' => 'app-a',
            'scenario_key' => 'scenario-a',
            'profile_id' => 1,
        ]));

        $this->assertSame('rejected', $result->status);
        $this->assertSame('operation_request_invalid', $result->errorCode);
        $this->assertSame([
            'component_key', 'suite_key', 'variant_key',
        ], array_keys($result->errors));
        $this->assertSame(0, $calls);
    }

    public function test_direct_catalog_result_and_command_projection_have_the_same_version_two_semantics(): void
    {
        [$provider] = $this->bindProvider();
        $direct = $this->app->make(AcceptanceOperationService::class)->execute(
            new OperationRequest('acceptance.list', ['limit' => 1000]),
        );
        $this->assertSame('succeeded', $direct->status);
        $this->assertInstanceOf(CatalogOperationData::class, $direct->data);

        $this->assertSame(0, Artisan::call('acceptance:list'));
        $command = json_decode(trim(Artisan::output()), true, flags: JSON_THROW_ON_ERROR);
        $data = $direct->data;

        $this->assertSame(2, $command['schema_version']);
        $this->assertSame('listed', $command['status']);
        $this->assertSame($data->planVersion, $command['plan_version']);
        $this->assertSame($data->catalogVersions, $command['catalog_versions']);
        $this->assertSame($data->matched, $command['counts']['matched']);
        $this->assertSame($data->executable, $command['counts']['executable']);
        $this->assertSame($data->excluded, $command['counts']['excluded']);
        $this->assertSame($data->byDisposition, $command['counts']['by_disposition']);
        $this->assertSame($data->fingerprint, $command['fingerprint']);
        $this->assertSame(array_map(fn ($item) => $item->toArray(), $data->items), $command['items']);
        $this->assertNull($direct->operationId);
        $this->assertSame([], $provider->resolutions);
        $this->assertDatabaseCount('tests', 0);
    }

    public function test_empty_catalog_versions_keep_map_shape_across_direct_and_command_boundaries(): void
    {
        $direct = $this->app->make(AcceptanceOperationService::class)->execute(
            new OperationRequest('acceptance.list'),
        );
        $this->assertInstanceOf(CatalogOperationData::class, $direct->data);
        $this->assertSame([], $direct->data->catalogVersions);

        $this->assertSame(0, Artisan::call('acceptance:list'));
        $command = json_decode(trim(Artisan::output()), false, flags: JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(\stdClass::class, $command->catalog_versions);
        $this->assertSame($direct->data->fingerprint, $command->fingerprint);
        $this->assertSame([], $command->items);
    }

    public function test_direct_run_result_and_command_projection_share_the_complete_identity_and_status(): void
    {
        [$provider, $scenario] = $this->bindProvider();
        $profile = Profile::factory()->create(['user_id' => User::factory()->create()->getKey()]);
        $test = $profile->tests()->create([
            'name' => 'Scenario A',
            'app_key' => 'app-a',
            'component_key' => 'component-a',
            'suite_key' => 'suite-a',
            'scenario_key' => 'scenario-a',
            'variant_key' => 'variant-a',
            'status' => TestStatusEnum::FINISHED,
        ]);
        $runService = Mockery::mock(AcceptanceRunService::class);
        $runService->shouldReceive('run')->twice()->withArgs(
            fn (Profile $actualProfile, AcceptanceExecutionIdentity $identity, AcceptanceScenario $actualScenario): bool => $actualProfile->is($profile)
                && $identity->value() === (new AcceptanceExecutionIdentity(
                    'app-a',
                    'component-a',
                    'suite-a',
                    'scenario-a',
                    'variant-a',
                ))->value()
                && $actualScenario === $scenario,
        )->andReturn($test);
        $this->app->instance(AcceptanceRunService::class, $runService);
        $parameters = [
            'app_key' => 'app-a',
            'component_key' => 'component-a',
            'suite_key' => 'suite-a',
            'scenario_key' => 'scenario-a',
            'variant_key' => 'variant-a',
            'profile_id' => $profile->getKey(),
        ];

        $direct = $this->app->make(AcceptanceOperationService::class)->execute(
            new OperationRequest('acceptance.run', $parameters),
        );
        $this->assertSame('succeeded', $direct->status);
        $this->assertInstanceOf(RunOperationData::class, $direct->data);

        $this->assertSame(0, Artisan::call('acceptance:run', [
            'app' => 'app-a',
            'component' => 'component-a',
            'suite' => 'suite-a',
            'scenario' => 'scenario-a',
            'variant' => 'variant-a',
            'profile' => $profile->getKey(),
        ]));
        $command = json_decode(trim(Artisan::output()), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame([
            'schema_version' => 2,
            'status' => $direct->data->testStatus->value,
            'test_id' => $direct->data->testId,
            'app_key' => $direct->data->appKey,
            'component_key' => $direct->data->componentKey,
            'suite_key' => $direct->data->suiteKey,
            'scenario_key' => $direct->data->scenarioKey,
            'variant_key' => $direct->data->variantKey,
            'error_code' => $direct->data->errorCode,
        ], $command);
        $this->assertSame(2, $direct->version);
        $this->assertTrue(OperationResult::isUuid($direct->correlationId));
        $this->assertTrue(OperationResult::isUuid($direct->operationId));
        $this->assertSame([
            ['component-a', 'suite-a', 'scenario-a', 'variant-a'],
            ['component-a', 'suite-a', 'scenario-a', 'variant-a'],
        ], $provider->resolutions);
    }

    /** @return array{AcceptanceComponentProvider, AcceptanceScenario} */
    private function bindProvider(): array
    {
        $metadata = new ScenarioMetadata(
            ['contract'],
            ['command'],
            AutomationDisposition::AUTOMATED,
            EvidenceMode::METADATA_ONLY,
        );
        $scenario = new class($metadata) implements AcceptanceScenario
        {
            public function __construct(private readonly ScenarioMetadata $scenarioMetadata) {}

            public function key(): string
            {
                return 'scenario-a';
            }

            public function name(): string
            {
                return 'Scenario A';
            }

            public function metadata(): ScenarioMetadata
            {
                return $this->scenarioMetadata;
            }

            public function steps(TestContext $context): iterable
            {
                return [];
            }
        };
        $provider = new class($scenario, $metadata) implements AcceptanceComponentProvider
        {
            public array $resolutions = [];

            public function __construct(
                private readonly AcceptanceScenario $scenario,
                private readonly ScenarioMetadata $scenarioMetadata,
            ) {}

            public function key(): string
            {
                return 'app-a';
            }

            public function catalogVersion(): string
            {
                return 'v2';
            }

            public function components(): iterable
            {
                yield new ComponentDescriptor('component-a');
            }

            public function suites(): iterable
            {
                yield new SuiteDescriptor('suite-a', 'component-a');
            }

            public function scenarios(): iterable
            {
                yield new ScenarioDescriptor('scenario-a', 'component-a', 'suite-a', $this->scenarioMetadata);
            }

            public function variants(string $scenarioKey): iterable
            {
                yield new VariantDescriptor('variant-a');
            }

            public function resolveScenario(
                string $componentKey,
                string $suiteKey,
                string $scenarioKey,
                string $variantKey,
            ): ?AcceptanceScenario {
                $this->resolutions[] = [$componentKey, $suiteKey, $scenarioKey, $variantKey];

                return $this->scenario;
            }
        };
        $registry = new AcceptanceAppRegistry;
        $registry->register($provider);
        $this->app->instance(AcceptanceAppRegistry::class, $registry);

        return [$provider, $scenario];
    }
}
