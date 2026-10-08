<?php

namespace Tests\Feature;

use App\Acceptance\Operations\Data\OperationResult;
use App\Contracts\AcceptanceCatalogProvider;
use App\Data\AcceptanceSelector;
use App\Data\ScenarioDescriptor;
use App\Data\VariantDescriptor;
use App\Exceptions\AcceptanceCatalogException;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceCatalog;
use App\Services\AcceptancePlanner;
use App\Services\AcceptanceRunService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use Modules\Core\Contracts\AcceptanceScenario;
use Modules\Core\Contracts\BrowserFactory;
use Modules\Core\Data\ScenarioMetadata;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Modules\Core\Services\AcceptanceRunner;
use RuntimeException;
use stdClass;
use Tests\TestCase;

class AcceptanceCatalogCommandTest extends TestCase
{
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
        // Preserve Laravel test-trait initialization while selecting a deliberately absent file.
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

    private ?ConnectionResolverInterface $originalResolver = null;

    private int $executionBoundaryCalls = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalResolver = Model::getConnectionResolver();
        $database = Mockery::mock(DatabaseManager::class);
        $database->shouldNotReceive('connection');
        DB::swap($database);
        Model::setConnectionResolver($database);
        foreach ([AcceptanceRunService::class, AcceptanceRunner::class, BrowserFactory::class] as $boundary) {
            $this->app->bind($boundary, function () {
                $this->executionBoundaryCalls++;

                throw new RuntimeException('Inspection crossed an execution boundary.');
            });
        }
        Log::spy();
    }

    protected function tearDown(): void
    {
        if ($this->originalResolver !== null) {
            Model::setConnectionResolver($this->originalResolver);
        }
        parent::tearDown();
    }

    public function test_commands_are_auto_discovered_with_only_the_approved_selector_options(): void
    {
        foreach (['acceptance:list', 'acceptance:plan'] as $name) {
            $definition = Artisan::all()[$name]->getDefinition();
            $this->assertSame([], $definition->getArguments());
            foreach (['app', 'scenario', 'variant', 'suite', 'capability', 'tag', 'disposition', 'evidence-mode'] as $option) {
                $this->assertTrue($definition->getOption($option)->isArray());
            }
            $this->assertSame('1000', $definition->getOption('limit')->getDefault());
            $this->assertFalse($definition->hasOption('browser'));
            $this->assertFalse($definition->hasOption('profile'));
        }
    }

    public function test_list_and_plan_have_exact_safe_shapes_counts_and_same_fingerprint_without_any_execution_state(): void
    {
        $app = $this->bindProvider();
        $list = $this->invoke('acceptance:list');
        $plan = $this->invoke('acceptance:plan');
        $this->assertSame([
            'status', 'plan_version', 'catalog_versions', 'counts', 'fingerprint', 'items',
        ], array_keys($list));
        $this->assertSame('listed', $list['status']);
        $this->assertSame('planned', $plan['status']);
        $this->assertSame(1, $plan['plan_version']);
        $this->assertSame(['local-app' => 'v1'], $plan['catalog_versions']);
        $this->assertSame([
            'matched' => 4, 'executable' => 1, 'excluded' => 3,
            'by_disposition' => ['automated' => 1, 'manual-only' => 1, 'blocked' => 1, 'not-implemented' => 1],
        ], $plan['counts']);
        $this->assertCount(4, $list['items']);
        $this->assertCount(1, $plan['items']);
        $this->assertSame('automated', $plan['items'][0]['disposition']);
        $this->assertTrue($plan['items'][0]['executable']);
        $this->assertSame($list['fingerprint'], $plan['fingerprint']);
        foreach ($list['items'] as $item) {
            $this->assertSame([
                'app_key', 'scenario_key', 'variant_key', 'suites', 'capabilities', 'tags',
                'disposition', 'evidence_mode', 'executable',
            ], array_keys($item));
        }
        $canonical = (new AcceptancePlanner(new AcceptanceCatalog($this->app->make(AcceptanceAppRegistry::class))))
            ->plan(new AcceptanceSelector)->canonicalPayload();
        $this->assertSafe(json_encode($canonical, JSON_THROW_ON_ERROR));
        $this->assertNoProviderExecution($app);
        Log::shouldNotHaveReceived('log');
        Log::shouldNotHaveReceived('error');
        Log::shouldNotHaveReceived('warning');
    }

    public function test_empty_and_repeated_exact_selectors_preserve_cli_contract_and_counts(): void
    {
        $app = $this->bindProvider();
        $empty = $this->invoke('acceptance:plan', [
            '--scenario' => ['automated'], '--disposition' => ['manual-only'],
        ]);
        $this->assertSame([], $empty['items']);
        $this->assertSame(0, $empty['counts']['matched']);
        $selected = $this->invoke('acceptance:list', [
            '--scenario' => ['manual-only', 'automated', 'automated'],
            '--tag' => ['tag'], '--evidence-mode' => ['metadata-only'],
        ]);
        $this->assertSame(['automated', 'manual-only'], array_column($selected['items'], 'scenario_key'));
        $this->assertSame(2, $selected['counts']['matched']);
        $this->assertSame(1, $selected['counts']['excluded']);
        $this->assertNoProviderExecution($app);
    }

    public function test_all_inspection_errors_have_fixed_codes_exits_and_safe_structured_events(): void
    {
        $cases = [
            ['normal', ['--tag' => ['example-token-sentinel/invalid']], 'acceptance_selector_invalid', 2],
            ['normal', ['--app' => ['example-credential-sentinel']], 'acceptance_app_not_found', 2],
            ['normal', ['--suite' => ['example-session-sentinel']], 'acceptance_selector_not_found', 2],
            ['normal', ['--scenario' => ['missing']], 'acceptance_selector_not_found', 2],
            ['normal', ['--variant' => ['missing']], 'acceptance_selector_not_found', 2],
            ['normal', ['--capability' => ['missing']], 'acceptance_selector_not_found', 2],
            ['normal', ['--tag' => ['missing']], 'acceptance_selector_not_found', 2],
            ['normal', ['--limit' => '1001'], 'acceptance_selector_invalid', 2],
            ['invalid', [], 'acceptance_catalog_invalid', 1],
            ['invalid-version', [], 'acceptance_catalog_invalid', 1],
            ['invalid-metadata', [], 'acceptance_catalog_invalid', 1],
            ['duplicate', [], 'acceptance_catalog_duplicate', 1],
            ['normal', ['--limit' => '1'], 'acceptance_catalog_limit_exceeded', 2],
            ['changed', [], 'acceptance_catalog_changed', 1],
            ['failure', [], 'acceptance_catalog_failed', 1],
        ];
        foreach (['acceptance:list', 'acceptance:plan'] as $command) {
            foreach ($cases as [$mode, $options, $code, $exit]) {
                Log::swap(Mockery::spy(LogManager::class));
                $app = $this->bindProvider();
                $app->mode = $mode;
                $result = $this->invoke($command, $options, $exit);
                $this->assertSame(['status' => $exit === 2 ? 'rejected' : 'failed', 'error_code' => $code], $result);
                Log::shouldHaveReceived('log')->once()->with(
                    $exit === 2 ? 'warning' : 'error',
                    'tms.acceptance.operation.failed',
                    Mockery::on(fn (array $context): bool => $this->operationLog($context,
                        $command === 'acceptance:list' ? 'acceptance.list' : 'acceptance.plan', $code)),
                );
                $this->assertNoProviderExecution($app);
            }
        }
    }

    public function test_raw_provider_exceptions_and_unknown_inputs_never_enter_output_or_normalized_errors(): void
    {
        $app = $this->bindProvider();
        $app->mode = 'failure';
        $this->invoke('acceptance:plan', [], 1);
        try {
            (new AcceptancePlanner(new AcceptanceCatalog($this->app->make(AcceptanceAppRegistry::class))))
                ->plan(new AcceptanceSelector);
            $this->fail('Expected a normalized exception.');
        } catch (AcceptanceCatalogException $exception) {
            $this->assertSame('acceptance_catalog_failed', $exception->errorCode);
            $this->assertNull($exception->getPrevious());
            $this->assertSafe($exception->getMessage());
        }
        $this->assertNoProviderExecution($app);
    }

    public function test_catalog_eligibility_failure_in_existing_run_command_precedes_profile_and_factory_lookup(): void
    {
        $app = $this->bindProvider();
        $service = Mockery::mock(AcceptanceRunService::class);
        $service->shouldNotReceive('run');
        $this->app->instance(AcceptanceRunService::class, $service);
        $result = $this->invoke('acceptance:run', [
            'app' => 'local-app', 'scenario' => 'manual-only', 'profile' => 'example-token-sentinel',
        ], 2);
        $this->assertSame(['status' => 'rejected', 'error_code' => 'acceptance_variant_not_executable'], $result);
        Log::shouldHaveReceived('log')->once()->with('warning', 'tms.acceptance.operation.failed',
            Mockery::on(fn (array $context): bool => $this->operationLog($context,
                'acceptance.run', 'acceptance_variant_not_executable')));
        $this->assertNoProviderExecution($app);
    }

    private function invoke(string $command, array $options = [], int $exit = 0): array
    {
        $this->assertSame($exit, Artisan::call($command, $options));
        $output = trim(Artisan::output());
        $this->assertSame(0, substr_count($output, "\n"));
        $this->assertSafe($output);

        return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    }

    private function operationLog(array $context, string $operation, string $code): bool
    {
        $this->assertSame($operation, $context['operation']);
        $this->assertSame($code, $context['error_code']);
        $this->assertTrue(OperationResult::isUuid($context['correlation_id']));
        $this->assertSame($operation === 'acceptance.run', $context['operation_id'] !== null);
        if ($context['operation_id'] !== null) {
            $this->assertTrue(OperationResult::isUuid($context['operation_id']));
        }
        $this->assertSafe(json_encode($context, JSON_THROW_ON_ERROR));
        $this->assertSame([], array_diff(array_keys($context), [
            'operation', 'error_code', 'correlation_id', 'operation_id', 'retryable',
            'permanent', 'admin_action_required', 'exception_class',
        ]));

        return true;
    }

    private function assertSafe(string $text): void
    {
        foreach (['example-credential-sentinel', 'example-session-sentinel', 'example-token-sentinel'] as $sentinel) {
            $this->assertStringNotContainsString($sentinel, $text);
        }
    }

    private function assertNoProviderExecution(AcceptanceCatalogProvider $app): void
    {
        $this->assertSame(0, $app->resolutions);
        $this->assertSame(0, $app->legacyCalls);
        $this->assertSame(0, $app->accountCalls);
        $this->assertSame(0, $this->executionBoundaryCalls);
    }

    private function bindProvider(): AcceptanceCatalogProvider
    {
        $app = new class implements AcceptanceCatalogProvider
        {
            public string $mode = 'normal';

            public string $revision = 'v1';

            public int $resolutions = 0;

            public int $legacyCalls = 0;

            public int $accountCalls = 0;

            private string $privateState = 'example-credential-sentinel|example-session-sentinel|example-token-sentinel';

            public function key(): string
            {
                return 'local-app';
            }

            public function catalogVersion(): string
            {
                if ($this->mode === 'invalid-version') {
                    return null;
                }

                return $this->revision;
            }

            public function descriptors(): iterable
            {
                if ($this->mode === 'invalid') {
                    yield new stdClass;

                    return;
                }
                if ($this->mode === 'failure') {
                    throw new RuntimeException($this->privateState);
                }
                foreach (AutomationDisposition::cases() as $disposition) {
                    $descriptor = new ScenarioDescriptor($disposition->value, new ScenarioMetadata(
                        ['suite'], ['cap'], [$this->mode === 'invalid-metadata' ? $this->privateState : 'tag'],
                        $disposition, EvidenceMode::METADATA_ONLY,
                    ));
                    yield $descriptor;
                    if ($this->mode === 'duplicate') {
                        yield $descriptor;
                    }
                }
                if ($this->mode === 'changed') {
                    $this->revision = 'v2';
                }
            }

            public function variants(string $scenarioKey): iterable
            {
                yield new VariantDescriptor('default');
            }

            private function acquireAccount(): void
            {
                $this->accountCalls++;
                throw new RuntimeException($this->privateState);
            }

            public function resolveScenario(string $scenarioKey, string $variantKey): ?AcceptanceScenario
            {
                $this->resolutions++;
                $this->acquireAccount();

                return null;
            }

            public function scenarios(): iterable
            {
                $this->legacyCalls++;
                throw new RuntimeException($this->privateState);
            }
        };
        $registry = new AcceptanceAppRegistry;
        $registry->register($app);
        $this->app->instance(AcceptanceAppRegistry::class, $registry);

        return $app;
    }
}
