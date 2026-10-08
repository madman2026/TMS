<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AcceptanceOperationApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'acceptance_operation_request_id',
        'scope',
        'schema_version',
        'input_fingerprint',
        'actor_type',
        'actor_reference',
        'approved_at',
        'expires_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(AcceptanceOperationRequest::class, 'acceptance_operation_request_id');
    }
}
