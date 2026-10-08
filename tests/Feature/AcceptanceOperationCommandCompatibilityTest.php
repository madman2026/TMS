<?php

namespace Tests\Feature;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Data\CatalogOperationData;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Operations\Data\RunOperationData;
use App\Contracts\AcceptanceCatalogProvider;
use App\Data\ScenarioDescriptor;
use App\Data\VariantDescriptor;
use App\Models\Profile;
use App\Models\User;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceRunService;
use App\TestStatusEnum;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Mockery;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\BrowserFactory;
use Modules\Core\Contracts\StepResult;
use Modules\Core\Data\RunOptions;
use Modules\Core\Data\RunResult;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Modules\Core\Services\AcceptanceRunner;
use RuntimeException;
use Tests\TestCase;

class AcceptanceOperationCommandCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    private string $lastOutput = '';

    public function createApplication()
    {
        foreach (['bootstrap/cache/config.php', 'bootstrap/cache/pack-0009-config-disabled.php',
            '__tms_pack_0009_no_dotenv__', '__tms_pack_0009_no_dotenv__.testing'] as $guard) {
            if (file_exists(__DIR__.'/../../'.$guard)) {
                throw new RuntimeException('Pack 0009 isolation guard failed.');
            }
        }
        if (getenv('APP_ENV') !== 'testing' || getenv('DB_CONNECTION') !== 'sqlite'
            || getenv('DB_DATABASE') !== ':memory:') {
            throw new RuntimeException('Pack 0009 requires disposable testing configuration.');
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        if (file_exists($app->getCachedConfigPath())) {
            throw new RuntimeException('Pack 0009 isolation guard failed.');
        }
        // Prove the database boundary before Laravel initializes RefreshDatabase.
        $this->traitsUsedByTest = array_flip(class_uses_recursive(static::class));
        $app->loadEnvironmentFrom('__tms_pack_0009_no_dotenv__');
        // PHPUnit observes suppressed warnings from dotenv's absent-file probe, too.
        // Handle only that guarded missing-file warning and forward every other error.
        $previousHandler = null;
        $previousHandler = set_error_handler(static function ($severity, $message, $file, $line) use (&$previousHandler) {
            if ($severity === E_WARNING && str_contains($message, '__tms_pack_0009_no_dotenv__')
                && str_ends_with(str_replace('\\', '/', $file), '/vendor/vlucas/phpdotenv/src/Store/File/Reader.php')) {
                return true;
            }

            return $previousHandler !== null ? $previousHandler($severity, $message, $file, $line) : false;
        });
        try {
            $app->make(Kernel::class)->bootstrap();
        } finally {
            restore_error_handler();
        }
        if ($app->environment() !== 'testing' || $app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException('Pack 0009 booted an unsafe database configuration.');
        }

        return $app;
    }

    public function test_direct_catalog_properties_match_legacy_cli_projections_without_execution_or_profile_lookup(): void
    {
        $provider = $this->provider();
        $calls = 0;
        foreach ([AcceptanceRunService::class, AcceptanceRunner::class, BrowserFactory::class] as $boundary) {
            $this->app->bind($boundary, function () use (&$calls) {
                $calls++;
                throw new RuntimeException('Inspection crossed an execution boundary.');
            });
        }
        $resolver = Model::getConnectionResolver();
        $database = Mockery::mock(DatabaseManager::class);
        $database->shouldNotReceive('connection');
        Model::setConnectionResolver($database);
        Log::spy();
        try {
            $service = $this->app->make(AcceptanceOperationService::class);
            foreach (['list', 'plan'] as $mode) {
                $direct = $service->execute(new OperationRequest('acceptance.'.$mode, ['limit' => 1000]));
                $this->assertSame('succeeded', $direct->status);
                $this->assertInstanceOf(CatalogOperationData::class, $direct->data);
                $cli = $this->invoke('acceptance:'.$mode);
                $data = $direct->data;
                $this->assertSame(['status', 'plan_version', 'catalog_versions', 'counts', 'fingerprint', 'items'], array_keys($cli));
                $this->assertSame($mode === 'list' ? 'listed' : 'planned', $cli['status']);
                $this->assertSame($data->planVersion, $cli['plan_version']);
                $this->assertSame($data->catalogVersions, $cli['catalog_versions']);
                $this->assertSame([$data->matched, $data->executable, $data->excluded],
                    [$cli['counts']['matched'], $cli['counts']['executable'], $cli['counts']['excluded']]);
                $this->assertSame($data->byDisposition, $cli['counts']['by_disposition']);
                $this->assertSame($data->fingerprint, $cli['fingerprint']);
                $this->assertSame(array_map(fn ($item) => $item->toArray(), $data->items), $cli['items']);
                $this->assertSame(4, $data->matched);
                $this->assertSame(2, $data->executable);
                $this->assertCount($mode === 'list' ? 4 : 2, $data->items);
                $this->assertNull($direct->operationId);
            }
        } finally {
            Model::setConnectionResolver($resolver);
        }
        $this->assertSame([], $provider->resolutions);
        $this->assertSame(0, $calls);
        $this->assertDatabaseCount('tests', 0);
        $this->assertDatabaseCount('steps', 0);
        Log::shouldNotHaveReceived('log');
        Log::shouldNotHaveReceived('info');
    }

    public function test_empty_catalog_versions_remain_a_json_object_and_integer_limit_preserves_domain_validation(): void
    {
        $service = $this->app->make(AcceptanceOperationService::class);
        $direct = $service->execute(new OperationRequest('acceptance.list'));
        $this->assertSame([], $direct->data->catalogVersions);
        $cli = $this->invoke('acceptance:list');
        $decodedObject = json_decode($this->lastOutput, false, 512, JSON_THROW_ON_ERROR);
        $this->assertInstanceOf(\stdClass::class, $decodedObject->catalog_versions);
        $this->assertSame($direct->data->fingerprint, $cli['fingerprint']);
        foreach ([0, 1001, '00001'] as $limit) {
            $invalid = $service->execute(new OperationRequest('acceptance.plan', ['limit' => $limit]));
            $this->assertSame('acceptance_selector_invalid', $invalid->errorCode);
            $cli = $this->invoke('acceptance:plan', ['--limit' => (string) $limit], 2);
            $this->assertSame($invalid->errorCode, $cli['error_code']);
        }
    }

    public function test_direct_run_and_cli_share_test_identity_status_safe_codes_options_and_default_variant(): void
    {
        $provider = $this->provider();
        $profile = $this->profile();
        config()->set(['core.acceptance.browser' => 'webkit', 'core.acceptance.headless' => false,
            'core.acceptance.timeout_ms' => 3210, 'core.acceptance.slow_mo_ms' => 9]);
        foreach ([[TestStatusEnum::FINISHED, null], [TestStatusEnum::FAILED, 'acceptance_step_failed'],
            [TestStatusEnum::FAILED, 'example-sensitive-code'], [TestStatusEnum::FAILED, null],
            [TestStatusEnum::PENDING, null]] as [$status, $rawCode]) {
            Log::spy();
            $test = $profile->tests()->create(['name' => 'Synthetic scenario', 'app_key' => 'safe-app',
                'scenario_key' => 'automated', 'status' => $status, 'error_code' => $rawCode,
                'data' => ['unsafe' => 'example-sensitive-value']]);
            $runService = Mockery::mock(AcceptanceRunService::class);
            $runService->shouldReceive('run')->twice()->withArgs(fn ($actualProfile, $app, $scenario, RunOptions $options): bool => $actualProfile->is($profile) && $app === $provider && $scenario->key() === 'automated'
                && $options->browser === 'webkit' && $options->headless === false
                && $options->timeoutMs === 3210 && $options->slowMoMs === 9)->andReturn($test);
            $this->app->instance(AcceptanceRunService::class, $runService);
            $direct = $this->app->make(AcceptanceOperationService::class)->execute(new OperationRequest('acceptance.run', [
                'app_key' => 'safe-app', 'scenario_key' => 'automated', 'profile_id' => $profile->getKey(),
                'browser' => '', 'timeout_ms' => 3210, 'slow_mo_ms' => 9,
            ]));
            $this->assertInstanceOf(RunOperationData::class, $direct->data);
            $cli = $this->invoke('acceptance:run', ['app' => 'safe-app', 'scenario' => 'automated',
                'profile' => $profile->getKey()], $status === TestStatusEnum::FINISHED ? 0 : 1);
            $this->assertSame(['status', 'test_id', 'app_key', 'scenario_key', 'error_code'], array_keys($cli));
            $this->assertSame([$direct->data->testStatus->value, $direct->data->testId,
                $direct->data->appKey, $direct->data->scenarioKey, $direct->data->errorCode], array_values($cli));
            $this->assertSame($test->getKey(), $direct->data->testId);
            $this->assertSame($rawCode === 'example-sensitive-code' ? 'acceptance_command_failed' : $rawCode, $direct->errorCode);
            $this->assertSame($rawCode, $test->fresh()->error_code);
            $this->assertSame($status === TestStatusEnum::FINISHED ? 'succeeded' : 'failed', $direct->status);
            $this->assertOperationLog($direct);
        }
        $this->assertSame(array_fill(0, 10, ['automated', 'default']), $provider->resolutions);
    }

    public function test_real_run_service_with_fake_runner_persists_only_safe_history_and_correlated_boundary_results(): void
    {
        $this->provider();
        $profile = $this->profile();
        $runner = Mockery::mock(AcceptanceRunner::class);
        $runner->shouldReceive('run')->twice()->andReturn(
            new RunResult('safe-app', 'automated', 'Synthetic scenario', true, 0.1,
                [new StepResult('safe-step', true, results: ['unsafe' => 'example-sensitive-value'])]),
            new RunResult('safe-app', 'automated', 'Synthetic scenario', false, 0.2,
                [new StepResult('safe-step', false, error: 'example-sensitive-value', errorCode: 'acceptance_step_failed')],
                'acceptance_step_failed'),
        );
        $this->app->instance(AcceptanceRunner::class, $runner);
        $this->app->bind(BrowserFactory::class, fn () => throw new RuntimeException('A real browser must not resolve.'));
        Log::spy();
        foreach ([TestStatusEnum::FINISHED, TestStatusEnum::FAILED] as $status) {
            $result = $this->app->make(AcceptanceOperationService::class)->execute(new OperationRequest('acceptance.run', [
                'app_key' => 'safe-app', 'scenario_key' => 'automated', 'profile_id' => $profile->getKey(),
            ]));
            $test = $profile->tests()->findOrFail($result->data->testId);
            $this->assertSame($status, $result->data->testStatus);
            $this->assertSame($status, $test->status);
            $this->assertSame($result->errorCode, $test->error_code);
            $this->assertNull($test->data);
            $this->assertCount(1, $test->steps);
            $this->assertNull($test->steps[0]->data);
            $this->assertSame($status, $test->steps[0]->status);
            $this->assertStringNotContainsString('example-sensitive', print_r($result, true));
            $this->assertOperationLog($result);
        }
        $this->assertDatabaseCount('tests', 2);
        $this->assertDatabaseCount('steps', 2);
    }

    public function test_rejection_order_and_pre_started_configuration_failure_match_direct_and_cli_exits(): void
    {
        $this->provider();
        $profile = $this->profile();
        $runService = Mockery::mock(AcceptanceRunService::class);
        $runService->shouldNotReceive('run');
        $this->app->instance(AcceptanceRunService::class, $runService);
        foreach ([['missing', 'missing', 'missing', 'acceptance_app_not_found'],
            ['safe-app', 'manual-only', 'missing', 'acceptance_variant_not_executable'],
            ['safe-app', 'automated', 'missing', 'acceptance_profile_not_found'],
            ['safe-app', 'automated', $profile->getKey(), 'acceptance_configuration_invalid']] as [$app, $scenario, $id, $code]) {
            $direct = $this->app->make(AcceptanceOperationService::class)->execute(new OperationRequest('acceptance.run', [
                'app_key' => $app, 'scenario_key' => $scenario, 'profile_id' => $id, 'timeout_ms' => '-1',
            ]));
            $cli = $this->invoke('acceptance:run', ['app' => $app, 'scenario' => $scenario, 'profile' => $id, '--timeout' => '-1'], 2);
            $this->assertSame($code, $direct->errorCode);
            $this->assertSame(['status' => 'rejected', 'error_code' => $code], $cli);
            $this->assertNull($direct->data);
            $this->assertTrue(OperationResult::isUuid($direct->operationId));
        }
        $this->assertDatabaseCount('tests', 0);
        $this->assertDatabaseCount('steps', 0);
    }

    public function test_started_execution_exceptions_are_safe_failures_without_invented_test_linkage(): void
    {
        $this->provider();
        $profile = $this->profile();
        foreach (['acceptance_configuration_invalid', 'acceptance_browser_start_failed', 'acceptance_scenario_failed',
            'acceptance_result_persistence_failed', 'example-sensitive-code'] as $rawCode) {
            Log::spy();
            $runService = Mockery::mock(AcceptanceRunService::class);
            $runService->shouldReceive('run')->twice()->andThrow(new AcceptanceExecutionException($rawCode, false,
                'example-sensitive-value', new RuntimeException('example-sensitive-chain')));
            $this->app->instance(AcceptanceRunService::class, $runService);
            $direct = $this->app->make(AcceptanceOperationService::class)->execute(new OperationRequest('acceptance.run', [
                'app_key' => 'safe-app', 'scenario_key' => 'automated', 'profile_id' => $profile->getKey(),
            ]));
            $code = $rawCode === 'example-sensitive-code' ? 'acceptance_command_failed' : $rawCode;
            $this->assertSame('failed', $direct->status);
            $this->assertSame($code, $direct->errorCode);
            $this->assertNull($direct->data);
            $this->assertSame(['status' => 'failed', 'error_code' => $code], $this->invoke('acceptance:run', [
                'app' => 'safe-app', 'scenario' => 'automated', 'profile' => $profile->getKey(),
            ], 1));
            $this->assertOperationLog($direct);
        }
        $this->assertDatabaseCount('tests', 0);
    }

    private function invoke(string $name, array $parameters = [], int $exit = 0): array
    {
        $this->assertSame($exit, Artisan::call($name, $parameters));
        $output = trim(Artisan::output());
        $this->lastOutput = $output;
        $this->assertSame(0, substr_count($output, "\n"));
        $this->assertStringNotContainsString('example-sensitive', $output);

        return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    }

    private function assertOperationLog(OperationResult $result): void
    {
        $expected = [
            'operation' => 'acceptance.run', 'error_code' => $result->errorCode,
            'correlation_id' => $result->correlationId, 'operation_id' => $result->operationId,
            'retryable' => $result->retryable, 'permanent' => $result->permanent,
            'admin_action_required' => $result->adminActionRequired,
        ];
        if ($result->data !== null) {
            $expected['test_id'] = $result->data->testId;
        }
        if ($result->status === 'succeeded') {
            Log::shouldHaveReceived('info')->with('tms.acceptance.operation.completed', $expected);
        } else {
            Log::shouldHaveReceived('log')->with($result->status === 'rejected' ? 'warning' : 'error',
                'tms.acceptance.operation.failed', $expected);
        }
    }

    private function profile(): Profile
    {
        return Profile::factory()->create(['user_id' => User::factory()->create()->getKey(),
            'extra' => ['unsafe' => 'example-sensitive-value']]);
    }

    private function provider(): AcceptanceCatalogProvider
    {
        $scenario = Mockery::mock(AcceptanceScenario::class);
        $metadata = new ScenarioMetadata(['suite'], ['capability'], ['tag'], AutomationDisposition::AUTOMATED, EvidenceMode::METADATA_ONLY);
        $scenario->shouldReceive('key')->andReturn('automated');
        $scenario->shouldReceive('name')->andReturn('Synthetic scenario');
        $scenario->shouldReceive('metadata')->andReturn($metadata);
        $scenario->shouldNotReceive('steps');
        $provider = new class($scenario, $metadata) implements AcceptanceCatalogProvider
        {
            public array $resolutions = [];

            public function __construct(private AcceptanceScenario $scenario, private ScenarioMetadata $metadata) {}

            public function key(): string
            {
                return 'safe-app';
            }

            public function catalogVersion(): string
            {
                return 'v1';
            }

            public function descriptors(): iterable
            {
                yield new ScenarioDescriptor('automated', $this->metadata);
                yield new ScenarioDescriptor('manual-only', new ScenarioMetadata(['suite'], ['capability'], ['tag'],
                    AutomationDisposition::MANUAL_ONLY, EvidenceMode::METADATA_ONLY));
            }

            public function variants(string $scenarioKey): iterable
            {
                yield new VariantDescriptor('default');
                yield new VariantDescriptor('other');
            }

            public function resolveScenario(string $scenarioKey, string $variantKey): ?AcceptanceScenario
            {
                $this->resolutions[] = [$scenarioKey, $variantKey];

                return $this->scenario;
            }

            public function scenarios(): iterable
            {
                throw new RuntimeException('Provider must not use legacy discovery.');
            }
        };
        $registry = new AcceptanceAppRegistry;
        $registry->register($provider);
        $this->app->instance(AcceptanceAppRegistry::class, $registry);

        return $provider;
    }
}
