<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('acceptance_execution_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('acceptance_batch_items')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignUuid('operation_id')->constrained('acceptance_execution_operations')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('test_id')->nullable()->unique()->constrained('tests')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('attempt_number');
            $table->uuid('execution_token')->unique();
            $table->string('state', 32);
            $table->string('executor_capability', 64);
            $table->string('executor_key', 64)->nullable();
            $table->unsignedInteger('infrastructure_attempts')->default(0);
            $table->boolean('executor_entered')->default(false);
            $table->string('error_code', 64)->nullable();
            $table->string('cleanup_error_code', 64)->nullable();
            $table->boolean('retryable')->nullable();
            $table->boolean('permanent')->nullable();
            $table->boolean('admin_action_required')->default(false);
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('heartbeat_at')->nullable();
            $table->timestamp('lease_expires_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'attempt_number'], 'acceptance_attempts_item_number_unique');
            $table->index(['state', 'lease_expires_at'], 'acceptance_attempts_state_lease_index');
            $table->index(['operation_id', 'state'], 'acceptance_attempts_operation_state_index');
            $table->index('created_at', 'acceptance_attempts_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acceptance_execution_attempts');
    }
};
