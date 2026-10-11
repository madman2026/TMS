<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Feature;

use App\Acceptance\Coverage\Data\SourceCaseMapping;
use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Modules\NwidartTargetModuleCreator;
use App\Acceptance\Modules\TargetModuleDefinition;
use App\Acceptance\Modules\TargetModuleValidator;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Tests\TestCase;

final class NotificationDeliveryCorpusImportTest extends TestCase
{
    private const CHUNKS = [
        '001' => [901, '7f6ad1db3a35afb7f2ea0762a855bdfe8fb667ff8623c68166990594fd78ddd1'],
        '002' => [1000, '35c791ce8f970b5d5203671b043e277da9e9024a318956e4f354e549da2b5ac1'],
        '003' => [1017, '586d6258c5a160fbfb43f41fde4fcd62761d1bbfd76860b8adc5fed51a6bb31b'],
        '004' => [1005, 'e266154d2a19f063900a6d88861240704258852316db796256f4b93143b42d02'],
        '005' => [921, 'aa36e6e3a4f9344755c3a9217fa175020b34807fcc9baf8b3f85d081304f97c0'],
        '006' => [979, '2b336c6c1b71a48891df0674dd7310a4da211b46fe80d3a90e91092bd5f8ca77'],
        '007' => [979, 'f47388d57721c0508aa2354a31fcda40c821f4b84a75206a7dce8432b6e33993'],
        '008' => [965, '195119d3fe20102fecc3cd2ed57fc6bb1283b6c1d318bd45d9d93e6c754b80e4'],
        '009' => [1140, 'e6a27ad5d55a1983d638490bbfe9c482b6736491a345f8063ae5c8c2df3abbef'],
        '010' => [1084, '6fcc483325bba01d710473a8a17939cbd2fb590d321b824706edb49416022bca'],
        '011' => [1001, '31e89652a9480a50c5a0150553c762e8609c76b0a65e0b73fabab5b63ba57a7c'],
        '012' => [987, 'cb580b5dc8998ae4ab8c0f62be63c225df0de781c4cc691c0883120461b9a4cb'],
        '013' => [917, 'd5fc1682ebfef9029ac326af6cc2eb39272f369c112503e771f070690478042e'],
        '014' => [917, '2dc4acf12e682e5697afd1627e1678f4c3032eb0c2b79a7d8d79f6ea55edd61f'],
        '015' => [917, '347a684641938476ab32fdb03b3b9ac4660203653944cb67a50a6864585293ea'],
        '016' => [917, '61d267ad0ad68ac4939f28cfbc61cece9877f46786118201a35ca98a065b13eb'],
        '017' => [971, 'aa704162109c5eac955ff547879337eaf74156e64d045ad8ddaebb10d79ae31c'],
        '018' => [949, 'e19218d7c944e5094cb3f1ada08ac23ae7244dc8ce1d6a25cbacbfaf204f46b0'],
        '019' => [1143, 'f4adac8553fd553963687bb58eaf23fc854da327d5ba4dd45dffabc96e2d4078'],
        '020' => [1074, '6cce6347e82fd31e6d2fd838cfd9365b03eb5b5c538100ebf3240136a7e40e1f'],
        '021' => [1059, 'eaef7b24796b09841231472bfaeda99dd925bee01062509cb7b9c3fb0de61900'],
        '022' => [1036, 'a1f045998dc5a5985b5499b946691fb6f1716fd5580059dbfde82900567c454c'],
        '023' => [1017, 'd064e7d08cc92e56cf5ab90d6431a9718587a54d8408431e4b1a71dcd6d27f37'],
        '024' => [1029, 'a6267e9422c78de207c85b2ff909f0d4da71e981d29ceaab88826beeceaca587'],
        '025' => [743, '3d1463110549877ae385d9740e9bc810b02fe67cd0da2fbd5128688fb4c1bbee'],
    ];

    private Filesystem $files;

    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->files = new Filesystem;
        $this->root = str_replace('\\', '/', sys_get_temp_dir()).'/tms-dk-corpus-import-'.Str::uuid();
        $this->files->makeDirectory($this->root, 0755, true);
    }

    protected function tearDown(): void
    {
        $resolved = realpath($this->root);
        $temporary = realpath(sys_get_temp_dir());
        if ($resolved !== false && $temporary !== false
            && str_starts_with(strtolower(str_replace('\\', '/', $resolved)), strtolower(str_replace('\\', '/', $temporary)).'/tms-dk-corpus-import-')) {
            $this->files->deleteDirectory($resolved);
        }
        Mockery::close();
        parent::tearDown();
    }

    public function test_manifest_preserves_all_exact_chunk_counts_and_hashes(): void
    {
        $manifest = $this->files->get(base_path('Modules/DK/docs/ai/references/ND-CORPUS-MANIFEST-0001.md'));
        preg_match_all(
            '/^\| (?<chunk>\d{3}) \| (?<records>[\d,]+) \| [\d,]+ \| `(?<hash>[a-f0-9]{64})` \|/m',
            $manifest,
            $rows,
            PREG_SET_ORDER,
        );

        $actual = [];
        foreach ($rows as $row) {
            $actual[$row['chunk']] = [(int) str_replace(',', '', $row['records']), $row['hash']];
        }

        $this->assertSame(self::CHUNKS, $actual);
        $this->assertSame(24668, array_sum(array_column(self::CHUNKS, 0)));
    }

    public function test_generated_file_set_matches_every_imported_scenario_and_remains_non_executable(): void
    {
        $component = $this->files->get(base_path(
            'Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php',
        ));
        preg_match_all("/ScenarioDescriptor\\('([^']+)'/", $component, $matches);
        $scenarioKeys = array_values(array_unique($matches[1], SORT_STRING));
        sort($scenarioKeys, SORT_STRING);

        $expected = array_map(static fn (string $key): string => Str::studly($key).'.php', $scenarioKeys);
        sort($expected, SORT_STRING);
        $directory = base_path('Modules/DK/app/Acceptance/Components/NotificationDelivery/Scenarios');
        $actual = array_map(static fn ($file): string => $file->getFilename(), $this->files->files($directory));
        sort($actual, SORT_STRING);

        $this->assertCount(139, $scenarioKeys);
        $this->assertSame($expected, $actual);
        foreach ($actual as $file) {
            $source = $this->files->get($directory.'/'.$file);
            $this->assertStringContainsString('AutomationDisposition::NOT_IMPLEMENTED', $source);
            $this->assertStringContainsString('public function steps(TestContext $context): iterable', $source);
            $this->assertStringContainsString('return [];', $source);
        }
    }

    public function test_synthetic_chunks_plan_apply_validate_and_replay_without_external_side_effects(): void
    {
        Http::preventStrayRequests();
        Notification::fake();
        Queue::fake();
        Event::fake();

        $modules = $this->modules();
        $config = $this->config();
        $validator = new TargetModuleValidator($modules, $this->files, $config);
        $creator = new NwidartTargetModuleCreator($modules, $this->files, $config, $validator);
        $creator->create(new TargetModuleDefinition('ExampleTarget', 'example-app'), false);
        $creator->createComponent('ExampleTarget', 'notification-delivery', false);
        $chunks = [
            [new SourceCaseMapping(
                'synthetic.case.001',
                CoverageDisposition::AUTOMATED_FULL,
                'notification-delivery',
                'admin.delivery',
                'synthetic-first',
                'v.0000000000000001',
                coveredAssertions: ['a.00000000000000000001'],
            )],
            [new SourceCaseMapping(
                'synthetic.case.002',
                CoverageDisposition::AUTOMATED_FULL,
                'notification-delivery',
                'admin.delivery',
                'synthetic-second',
                'v.0000000000000002',
                coveredAssertions: ['a.00000000000000000002'],
            )],
        ];

        foreach ($chunks as $chunk) {
            $firstPlan = $creator->importScenarios('ExampleTarget', $chunk);
            $secondPlan = $creator->importScenarios('ExampleTarget', $chunk);
            $this->assertEquals($firstPlan->changes, $secondPlan->changes);
            $this->assertSame(1, $firstPlan->created());
            $this->assertSame(2, $firstPlan->updated());
            $creator->importScenarios('ExampleTarget', $chunk, false);
        }

        foreach ($chunks as $chunk) {
            $replay = $creator->importScenarios('ExampleTarget', $chunk);
            $this->assertSame(0, $replay->created());
            $this->assertSame(0, $replay->updated());
            $this->assertSame(count($replay->changes), $replay->unchanged());
            foreach ($replay->changes as $change) {
                $this->assertTrue(
                    $change->path === 'app/Acceptance/Coverage/SourceCaseMappings.php'
                    || str_starts_with($change->path, 'app/Acceptance/Components/NotificationDelivery/'),
                );
            }
        }

        $this->assertTrue($validator->validate('ExampleTarget')->valid);
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
        Event::assertNothingDispatched();
    }

    private function modules(): RepositoryInterface
    {
        $root = $this->root;
        $files = $this->files;
        $modules = Mockery::mock(RepositoryInterface::class);
        $modules->shouldReceive('getPath')->andReturn($root);
        $modules->shouldReceive('has')->andReturnUsing(
            static fn (string $name): bool => $files->isDirectory($root.'/'.$name),
        );

        return $modules;
    }

    private function config(): ConfigRepository
    {
        return new ConfigRepository(['modules' => [
            'namespace' => 'Modules',
            'paths' => ['app_folder' => 'app/'],
            'composer' => [
                'vendor' => 'example-vendor',
                'author' => ['name' => 'Example Author', 'email' => 'author@example.test'],
            ],
        ]]);
    }
}
