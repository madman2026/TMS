<?php

namespace App\Models;

use App\Acceptance\Execution\Enums\OperationState;
use Database\Factories\AcceptanceExecutionOperationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AcceptanceExecutionOperation extends Model
{
    /** @use HasFactory<AcceptanceExecutionOperationFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $attributes = [
        'state' => OperationState::DRAFT->value,
        'lock_version' => 0,
        'admin_action_required' => false,
    ];

    protected $fillable = [
        'id', 'batch_id', 'correlation_id', 'origin', 'state', 'lock_version', 'error_code',
        'retryable', 'permanent', 'admin_action_required', 'queued_at', 'started_at',
        'finished_at', 'cancel_requested_at',
    ];

    protected function casts(): array
    {
        return [
            'batch_id' => 'integer',
            'state' => OperationState::class,
            'lock_version' => 'integer',
            'retryable' => 'boolean',
            'permanent' => 'boolean',
            'admin_action_required' => 'boolean',
            'queued_at' => 'immutable_datetime',
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
            'cancel_requested_at' => 'immutable_datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(AcceptanceBatch::class, 'batch_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AcceptanceExecutionAttempt::class, 'operation_id');
    }

    protected static function newFactory(): AcceptanceExecutionOperationFactory
    {
        return AcceptanceExecutionOperationFactory::new();
    }
}
