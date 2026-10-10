<?php

namespace Tests\Feature;

use App\Console\Acceptance\AcceptanceCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AcceptanceOperatorCommandsTest extends TestCase
{
    private const COMMANDS = [
        'acceptance:list', 'acceptance:plan', 'acceptance:run', 'acceptance:app:create',
        'acceptance:app:validate', 'acceptance:component:create', 'acceptance:scenarios:import',
        'acceptance:prerequisite:prepare', 'acceptance:prerequisite:input:submit',
        'acceptance:prerequisite:approval:grant', 'acceptance:prerequisite:request:cancel',
        'acceptance:batch:start', 'acceptance:batch:resume', 'acceptance:batch:item:retry',
        'acceptance:batch:cancel', 'acceptance:status', 'acceptance:report', 'acceptance:coverage',
    ];

    public function test_all_approved_operator_commands_are_registered_with_common_modes(): void
    {
        $commands = Artisan::all();
        foreach (self::COMMANDS as $name) {
            $this->assertArrayHasKey($name, $commands);
            $this->assertInstanceOf(AcceptanceCommand::class, $commands[$name], $name);
            $this->assertNotSame('', $commands[$name]->getDescription(), $name);
            $this->assertSame([], $commands[$name]->getAliases(), $name);
            $definition = $commands[$name]->getDefinition();
            $this->assertTrue($definition->hasOption('interactive'), $name);
            $this->assertTrue($definition->hasOption('json'), $name);
        }
    }

    public function test_acceptance_cli_source_is_ascii_only(): void
    {
        foreach (File::allFiles(app_path('Console')) as $file) {
            $contents = File::get($file->getPathname());

            $this->assertSame(0, preg_match('/[^\x00-\x7F]/', $contents), $file->getPathname());
        }
    }

    public function test_interactive_catalog_uses_english_laravel_prompts_and_human_output(): void
    {
        $this->artisan('acceptance:list', ['--interactive' => true])
            ->expectsChoice('Configure optional catalog filters?', [], [
                'app' => 'App keys',
                'component' => 'Component keys',
                'suite' => 'Suite keys',
                'scenario' => 'Scenario keys',
                'variant' => 'Variant keys',
                'capability' => 'Capabilities',
                'tag' => 'Tags',
                'disposition' => 'Automation dispositions',
                'evidence-mode' => 'Evidence modes',
                'limit' => 'Maximum matched items',
            ])
            ->expectsOutputToContain('Operation completed successfully.')
            ->assertSuccessful();
    }

    public function test_interactive_required_value_and_failure_guidance_are_english(): void
    {
        $this->artisan('acceptance:app:validate', ['--interactive' => true])
            ->expectsQuestion('Module name', 'DefinitelyMissingModule')
            ->expectsOutputToContain('Operation failed')
            ->doesntExpectOutputToContain('DefinitelyMissingModule')
            ->assertExitCode(2);
    }

    public function test_all_command_argument_signatures_are_exact_and_promptable(): void
    {
        $expected = [
            'acceptance:list' => [],
            'acceptance:plan' => [],
            'acceptance:run' => ['app', 'component', 'suite', 'scenario', 'variant', 'profile'],
            'acceptance:app:create' => ['module', 'app'],
            'acceptance:app:validate' => ['module'],
            'acceptance:component:create' => ['module', 'component'],
            'acceptance:scenarios:import' => ['module'],
            'acceptance:prerequisite:prepare' => ['app', 'component', 'suite', 'scenario', 'variant', 'profile'],
            'acceptance:prerequisite:input:submit' => ['request', 'lock-version'],
            'acceptance:prerequisite:approval:grant' => ['request', 'lock-version', 'scope'],
            'acceptance:prerequisite:request:cancel' => ['request', 'lock-version'],
            'acceptance:batch:start' => ['profile', 'mode'],
            'acceptance:batch:resume' => ['batch', 'operation', 'lock-version'],
            'acceptance:batch:item:retry' => ['item', 'lock-version'],
            'acceptance:batch:cancel' => ['batch', 'operation', 'lock-version'],
            'acceptance:status' => [],
            'acceptance:report' => ['batch'],
            'acceptance:coverage' => ['app'],
        ];

        foreach ($expected as $name => $arguments) {
            $definition = Artisan::all()[$name]->getDefinition();
            $this->assertSame($arguments, array_keys($definition->getArguments()), $name);
            foreach ($arguments as $argument) {
                $this->assertFalse($definition->getArgument($argument)->isRequired(), $name.' '.$argument);
            }
        }
    }

    public function test_script_mode_never_prompts_and_returns_bounded_local_errors(): void
    {
        $cases = [
            ['acceptance:app:create', [], 'cli_input_invalid'],
            ['acceptance:app:create', ['module' => 'Fixture', 'app' => 'fixture', '--apply' => true], 'cli_confirmation_required'],
            ['acceptance:scenarios:import', ['module' => 'Fixture'], 'cli_input_invalid'],
            ['acceptance:prerequisite:input:submit', [
                'request' => 'dcb1cf9d-207c-4a44-963b-000000000009',
                'lock-version' => 0,
            ], 'cli_input_invalid'],
            ['acceptance:status', [], 'cli_input_invalid'],
            ['acceptance:batch:item:retry', ['item' => 1, 'lock-version' => 0], 'cli_confirmation_required'],
        ];

        foreach ($cases as [$command, $parameters, $code]) {
            $this->assertSame(2, Artisan::call($command, [...$parameters, '--no-interaction' => true]));
            $this->assertSame([
                'schema_version' => 2,
                'status' => 'rejected',
                'error_code' => $code,
            ], json_decode(trim(Artisan::output()), true, flags: JSON_THROW_ON_ERROR));
        }
    }

    public function test_conflicting_or_noninteractive_interactive_mode_is_rejected_as_json(): void
    {
        foreach ([
            ['--interactive' => true, '--json' => true],
            ['--interactive' => true, '--no-interaction' => true],
        ] as $options) {
            $this->assertSame(2, Artisan::call('acceptance:list', $options));
            $this->assertSame('cli_mode_invalid', json_decode(
                trim(Artisan::output()),
                true,
                flags: JSON_THROW_ON_ERROR,
            )['error_code']);
        }
    }

    public function test_ci_never_enters_prompt_mode(): void
    {
        $previous = getenv('CI');
        putenv('CI=1');

        try {
            $this->assertSame(2, Artisan::call('acceptance:list', ['--interactive' => true]));
            $this->assertSame('cli_mode_invalid', json_decode(
                trim(Artisan::output()),
                true,
                flags: JSON_THROW_ON_ERROR,
            )['error_code']);
        } finally {
            $previous === false ? putenv('CI') : putenv('CI='.$previous);
        }
    }

    public function test_mutating_scaffolding_requires_both_apply_and_confirmation_in_script_mode(): void
    {
        $this->assertSame(2, Artisan::call('acceptance:component:create', [
            'module' => 'MissingFixture',
            'component' => 'component-a',
            '--apply' => true,
            '--no-interaction' => true,
        ]));
        $this->assertSame('cli_confirmation_required', json_decode(
            trim(Artisan::output()),
            true,
            flags: JSON_THROW_ON_ERROR,
        )['error_code']);
    }

    public function test_input_documents_are_bounded_and_never_echo_path_or_content(): void
    {
        foreach (['{"example-sensitive-value":', str_repeat('x', 65_537)] as $contents) {
            $path = tempnam(sys_get_temp_dir(), 'tms-cli-');
            $this->assertNotFalse($path);
            file_put_contents($path, $contents);

            try {
                $this->assertSame(2, Artisan::call('acceptance:prerequisite:input:submit', [
                    'request' => 'dcb1cf9d-207c-4a44-963b-000000000009',
                    'lock-version' => 0,
                    '--inputs' => $path,
                    '--no-interaction' => true,
                ]));
                $output = trim(Artisan::output());
                $this->assertSame('cli_input_invalid', json_decode(
                    $output,
                    true,
                    flags: JSON_THROW_ON_ERROR,
                )['error_code']);
                $this->assertStringNotContainsString($path, $output);
                $this->assertStringNotContainsString('example-sensitive-value', $output);
            } finally {
                @unlink($path);
            }
        }
    }
}
