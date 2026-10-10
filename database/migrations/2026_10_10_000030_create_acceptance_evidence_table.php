<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acceptance_evidence', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attempt_id')->constrained('acceptance_execution_attempts')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('type', 32);
            $table->string('reference_key', 191)->unique();
            $table->char('checksum_sha256', 64)->index();
            $table->unsignedBigInteger('size_bytes');
            $table->string('media_type', 127);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('duration_ms')->nullable();
            $table->timestamp('captured_at');
            $table->timestamp('available_until')->nullable()->index();
            $table->timestamps();

            $table->index(['attempt_id', 'captured_at'], 'acceptance_evidence_attempt_captured_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acceptance_evidence');
    }
};
