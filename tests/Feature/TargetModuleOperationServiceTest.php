<?php

namespace Tests\Feature;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Data\ModuleChangeData;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\TargetModuleValidationData;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mockery;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Tests\TestCase;

class TargetModuleOperationServiceTest extends TestCase
{
    private Filesystem $files;

    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->files = new Filesystem;
        $this->root = str_replace('\\', '/', sys_get_temp_dir()).'/tms-pack-0010-operation-'.Str::uuid();
        $this->files->makeDirectory($this->root, 0755, true);
        $root = $this->root;
        $modules = Mockery::mock(RepositoryInterface::class);
        $modules->shouldReceive('getPath')->andReturn($root);
        $modules->shouldReceive('has')->andReturnUsing(fn (string $name): bool => $this->files->isDirectory($root.'/'.$name));
        $this->app->instance(RepositoryInterface::class, $modules);
        $this->app['config']->set('modules.namespace', 'Modules');
        $this->app['config']->set('modules.paths.app_folder', 'app/');
        $this->app['config']->set('modules.composer.vendor', 'example');
        $this->app['config']->set('modules.composer.author', ['name' => 'Example', 'email' => 'example@example.test']);
        Log::swap(Mockery::spy(LogManager::class));
    }

    protected function tearDown(): void
    {
        if ($this->files->isDirectory($this->root) && str_contains($this->root, '/tms-pack-0010-operation-')) {
            $this->files->deleteDirectory($this->root);
        }
        Mockery::close();
        parent::tearDown();
    }

    public function test_four_operations_return_typed_results_and_stateful_traces(): void
    {
        $service = $this->app->make(AcceptanceOperationService::class);
        $create = $service->execute(new OperationRequest('acceptance.app.create', [
            'module_name' => 'ExampleTarget', 'app_key' => 'example-app', 'dry_run' => false,
        ]));
        $validate = $service->execute(new OperationRequest('acceptance.app.validate', [
            'module_name' => 'ExampleTarget',
        ]));
        $component = $service->execute(new OperationRequest('acceptance.component.create', [
            'module_name' => 'ExampleTarget', 'component_key' => 'billing', 'dry_run' => false,
        ]));
        $import = $service->execute(new OperationRequest('acceptance.scenarios.import', [
            'module_name' => 'ExampleTarget',
            'mappings' => [new SourceCaseMapping('case-1', CoverageDisposition::AUTOMATED_FULL,
                'billing', 'invoices', 'invoice-creates', 'default')],
            'dry_run' => false,
        ]));

        foreach ([$create, $component, $import] as $result) {
            $this->assertSame('succeeded', $result->status);
            $this->assertInstanceOf(ModuleChangeData::class, $result->data);
            $this->assertTrue($result->isUuid($result->operationId));
        }
        $this->assertSame('succeeded', $validate->status);
        $this->assertInstanceOf(TargetModuleValidationData::class, $validate->data);
        $this->assertNull($validate->operationId);
        Log::shouldHaveReceived('info')->times(3)->with(
            'tms.acceptance.operation.completed',
            Mockery::on(fn (array $context): bool => $context['module_name'] === 'ExampleTarget'
                && isset($context['change_count'])
                && ! array_key_exists('mappings', $context)),
        );
    }

    public function test_invalid_requests_and_generation_failures_are_safe_and_do_not_leak_values(): void
    {
        $service = $this->app->make(AcceptanceOperationService::class);
        $invalid = $service->execute(new OperationRequest('acceptance.app.create', [
            'module_name' => '../example-sensitive-value', 'app_key' => 'example-app',
        ]));
        $shape = $service->execute(new OperationRequest('acceptance.scenarios.import', [
            'module_name' => 'ExampleTarget', 'mappings' => ['example-sensitive-value'],
        ]));

        $this->assertSame('target_module_name_invalid', $invalid->errorCode);
        $this->assertSame('operation_request_invalid', $shape->errorCode);
        $this->assertStringNotContainsString('example-sensitive-value', print_r([$invalid, $shape], true));
        $this->assertSame([], glob($this->root.'/.tms-*') ?: []);
    }
}
