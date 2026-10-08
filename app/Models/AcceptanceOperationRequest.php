<?php

namespace App\Models;

use App\Acceptance\Prerequisites\Enums\PrerequisiteState;
use Database\Factories\AcceptanceOperationRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AcceptanceOperationRequest extends Model
{
    /** @use HasFactory<AcceptanceOperationRequestFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'correlation_id',
        'app_key',
        'component_key',
        'suite_key',
        'scenario_key',
        'variant_key',
        'profile_id',
        'schema_version',
        'schema_fingerprint',
        'state',
        'lock_version',
        'expires_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'profile_id' => 'integer',
            'state' => PrerequisiteState::class,
            'lock_version' => 'integer',
            'expires_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(AcceptanceOperationInput::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(AcceptanceOperationApproval::class);
    }

    protected static function newFactory(): AcceptanceOperationRequestFactory
    {
        return AcceptanceOperationRequestFactory::new();
    }
}
