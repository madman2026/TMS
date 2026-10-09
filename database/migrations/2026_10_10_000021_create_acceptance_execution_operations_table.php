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
        Schema::create('acceptance_execution_operations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('batch_id')->unique()->constrained('acceptance_batches')->restrictOnDelete()->cascadeOnUpdate();
            $table->uuid('correlation_id')->index();
            $table->string('origin', 16);
            $table->string('state', 32);
            $table->unsignedInteger('lock_version')->default(0);
            $table->string('error_code', 64)->nullable();
            $table->boolean('retryable')->nullable();
            $table->boolean('permanent')->nullable();
            $table->boolean('admin_action_required')->default(false);
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('cancel_requested_at')->nullable();
            $table->timestamps();

            $table->index(['state', 'updated_at'], 'acceptance_operations_state_updated_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acceptance_execution_operations');
    }
};
