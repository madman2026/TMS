<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acceptance_operation_approvals', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('acceptance_operation_request_id')
                ->constrained('acceptance_operation_requests')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('scope', 64);
            $table->string('schema_version', 64);
            $table->char('input_fingerprint', 64);
            $table->string('actor_type', 32);
            $table->string('actor_reference', 128);
            $table->timestamp('approved_at');
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['acceptance_operation_request_id', 'scope', 'schema_version', 'input_fingerprint'],
                'acceptance_approval_request_scope_schema_fingerprint_unique',
            );
            $table->index(
                ['acceptance_operation_request_id', 'scope', 'revoked_at'],
                'acceptance_approval_request_scope_revoked_index',
            );
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acceptance_operation_approvals');
    }
};
