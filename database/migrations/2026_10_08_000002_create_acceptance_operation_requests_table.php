<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acceptance_operation_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('correlation_id')->index();
            $table->string('app_key', 64);
            $table->string('component_key', 64);
            $table->string('suite_key', 64);
            $table->string('scenario_key', 64);
            $table->string('variant_key', 64);
            $table->foreignId('profile_id')->constrained('profiles')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('schema_version', 64);
            $table->char('schema_fingerprint', 64);
            $table->string('state', 32);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key'], 'acceptance_request_hierarchy_index');
            $table->index('profile_id', 'acceptance_request_profile_index');
            $table->index(['state', 'expires_at'], 'acceptance_request_state_expiry_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acceptance_operation_requests');
    }
};
