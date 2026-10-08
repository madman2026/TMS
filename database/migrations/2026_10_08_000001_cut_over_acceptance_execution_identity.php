<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->assertTestsTableIsEmpty();

        Schema::table('tests', function (Blueprint $table): void {
            $table->dropIndex('tests_app_scenario_created_index');
            $table->string('component_key', 64)->after('app_key');
            $table->string('suite_key', 64)->after('component_key');
            $table->string('variant_key', 64)->after('scenario_key');
            $table->string('app_key', 64)->nullable(false)->change();
            $table->string('scenario_key', 64)->nullable(false)->change();
            $table->index(
                ['app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key', 'created_at'],
                'tests_acceptance_identity_created_index',
            );
        });
    }

    public function down(): void
    {
        $this->assertTestsTableIsEmpty();

        Schema::table('tests', function (Blueprint $table): void {
            $table->dropIndex('tests_acceptance_identity_created_index');
            $table->string('app_key', 100)->nullable()->change();
            $table->string('scenario_key', 150)->nullable()->change();
            $table->dropColumn(['component_key', 'suite_key', 'variant_key']);
            $table->index(['app_key', 'scenario_key', 'created_at'], 'tests_app_scenario_created_index');
        });
    }

    private function assertTestsTableIsEmpty(): void
    {
        if (DB::table('tests')->exists()) {
            throw new RuntimeException('acceptance_identity_migration_requires_empty_tests');
        }
    }
};
