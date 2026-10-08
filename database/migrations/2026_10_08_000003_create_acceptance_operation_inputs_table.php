<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acceptance_operation_inputs', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('acceptance_operation_request_id')
                ->constrained('acceptance_operation_requests')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('key', 64);
            $table->string('schema_version', 64);
            $table->string('type', 32);
            $table->string('sensitivity', 32);
            $table->json('value_json')->nullable();
            $table->string('secret_reference', 255)->nullable();
            $table->char('value_fingerprint', 64);
            $table->string('submitted_by_type', 32);
            $table->string('submitted_by_reference', 128);
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(
                ['acceptance_operation_request_id', 'key', 'schema_version'],
                'acceptance_input_request_key_schema_unique',
            );
            $table->index(
                ['acceptance_operation_request_id', 'key'],
                'acceptance_input_request_key_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acceptance_operation_inputs');
    }
};
