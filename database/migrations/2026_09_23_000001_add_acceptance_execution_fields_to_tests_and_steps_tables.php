<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table): void {
            $table->string('app_key', 100)->nullable()->after('name');
            $table->string('scenario_key', 150)->nullable()->after('app_key');
            $table->string('error_code', 100)->nullable()->after('status');
            $table->index(['app_key', 'scenario_key', 'created_at'], 'tests_app_scenario_created_index');
        });

        Schema::table('steps', function (Blueprint $table): void {
            $table->string('status', 30)->nullable()->after('name');
            $table->boolean('critical')->default(true)->after('status');
            $table->string('error_code', 100)->nullable()->after('description');
            $table->text('error_message')->nullable()->after('error_code');
            $table->index(['test_id', 'status'], 'steps_test_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('steps', function (Blueprint $table): void {
            $table->dropIndex('steps_test_status_index');
            $table->dropColumn(['status', 'critical', 'error_code', 'error_message']);
        });

        Schema::table('tests', function (Blueprint $table): void {
            $table->dropIndex('tests_app_scenario_created_index');
            $table->dropColumn(['app_key', 'scenario_key', 'error_code']);
        });
    }
};
