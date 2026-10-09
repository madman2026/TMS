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
        Schema::create('acceptance_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_batch_id')->nullable()->constrained('acceptance_batches')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('profile_id')->constrained('profiles')->restrictOnDelete()->cascadeOnUpdate();
            $table->uuid('correlation_id')->index();
            $table->string('mode', 16);
            $table->unsignedSmallInteger('version');
            $table->char('plan_fingerprint', 64);
            $table->json('selector_snapshot');
            $table->json('catalog_versions');
            $table->string('state', 32);
            $table->unsignedInteger('lock_version')->default(0);
            $table->unsignedInteger('matched_count')->default(0);
            $table->unsignedInteger('executable_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('max_failures')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->unsignedInteger('next_dispatch_ordinal')->default(0);
            $table->uuid('laravel_batch_id')->nullable();
            $table->timestamp('cancel_requested_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('interrupted_at')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();

            $table->index(['state', 'updated_at'], 'acceptance_batches_state_updated_index');
            $table->index(['profile_id', 'created_at'], 'acceptance_batches_profile_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acceptance_batches');
    }
};
