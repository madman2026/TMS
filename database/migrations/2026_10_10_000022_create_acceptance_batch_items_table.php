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
        Schema::create('acceptance_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('acceptance_batches')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignUuid('acceptance_operation_request_id')->nullable()->constrained('acceptance_operation_requests')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('retry_of_item_id')->nullable()->constrained('acceptance_batch_items')->restrictOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('ordinal');
            $table->string('app_key', 64);
            $table->string('component_key', 64);
            $table->string('suite_key', 64);
            $table->string('scenario_key', 64);
            $table->string('variant_key', 64);
            $table->string('capability', 64);
            $table->string('catalog_version', 64);
            $table->json('classification_snapshot');
            $table->char('idempotency_key', 64);
            $table->string('state', 32);
            $table->unsignedInteger('lock_version')->default(0);
            $table->unsignedInteger('attempt_count')->default(0);
            $table->string('error_code', 64)->nullable();
            $table->string('cleanup_error_code', 64)->nullable();
            $table->boolean('retryable')->nullable();
            $table->boolean('permanent')->nullable();
            $table->boolean('admin_action_required')->default(false);
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'ordinal'], 'acceptance_items_batch_ordinal_unique');
            $table->unique(['batch_id', 'idempotency_key'], 'acceptance_items_batch_idempotency_unique');
            $table->unique(
                ['batch_id', 'app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key'],
                'acceptance_items_batch_identity_unique',
            );
            $table->index(['batch_id', 'state', 'ordinal'], 'acceptance_items_batch_state_ordinal_index');
            $table->index(
                ['app_key', 'component_key', 'suite_key', 'scenario_key', 'variant_key'],
                'acceptance_items_hierarchy_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acceptance_batch_items');
    }
};
