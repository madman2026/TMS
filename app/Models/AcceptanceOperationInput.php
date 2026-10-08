<?php

namespace App\Models;

use App\Acceptance\Prerequisites\Enums\InputSensitivity;
use App\Acceptance\Prerequisites\Enums\InputType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AcceptanceOperationInput extends Model
{
    use HasFactory;

    protected $fillable = [
        'acceptance_operation_request_id',
        'key',
        'schema_version',
        'type',
        'sensitivity',
        'value_json',
        'secret_reference',
        'value_fingerprint',
        'submitted_by_type',
        'submitted_by_reference',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => InputType::class,
            'sensitivity' => InputSensitivity::class,
            'value_json' => 'json',
            'submitted_at' => 'immutable_datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(AcceptanceOperationRequest::class, 'acceptance_operation_request_id');
    }
}
