<?php

namespace Tests\Unit;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class AcceptancePrerequisiteMigrationPortabilityTest extends TestCase
{
    public function test_prerequisite_foreign_key_names_fit_the_mysql_identifier_limit(): void
    {
        $cases = [
            'acceptance_operation_inputs' => [
                'migration' => '2026_10_08_000003_create_acceptance_operation_inputs_table.php',
                'foreign' => 'acceptance_input_request_foreign',
            ],
            'acceptance_operation_approvals' => [
                'migration' => '2026_10_08_000004_create_acceptance_operation_approvals_table.php',
                'foreign' => 'acceptance_approval_request_foreign',
            ],
        ];

        foreach ($cases as $table => $case) {
            $blueprint = null;
            Schema::shouldReceive('create')
                ->once()
                ->with($table, \Mockery::type(Closure::class))
                ->andReturnUsing(function (string $name, Closure $definition) use (&$blueprint): void {
                    $blueprint = new Blueprint(DB::connection(), $name);
                    $definition($blueprint);
                });

            $migration = require database_path('migrations/'.$case['migration']);
            $migration->up();

            $foreign = collect($blueprint?->getCommands())
                ->first(fn ($command): bool => $command->name === 'foreign');

            self::assertNotNull($foreign);
            self::assertSame($case['foreign'], $foreign->index);
            self::assertLessThanOrEqual(64, strlen($foreign->index));

            Schema::clearResolvedInstance('schema');
        }
    }
}
