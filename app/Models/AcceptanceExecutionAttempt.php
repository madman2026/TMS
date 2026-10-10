<?php

namespace App\Models;

use App\Acceptance\Execution\Enums\AttemptState;
use Database\Factories\AcceptanceExecutionAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AcceptanceExecutionAttempt extends Model
{
    /** @use HasFactory<AcceptanceExecutionAttemptFactory> */
    use HasFactory;

    protected $attributes = [
        'state' => AttemptState::QUEUED->value,
        'infrastructure_attempts' => 0,
        'executor_entered' => false,
        'admin_action_required' => false,
    ];

    protected $fillable = [
        'item_id', 'operation_id', 'test_id', 'attempt_number', 'execution_token', 'state',
        'executor_capability', 'executor_key', 'infrastructure_attempts', 'executor_entered',
        'error_code', 'cleanup_error_code', 'retryable', 'permanent', 'admin_action_required',
        'queued_at', 'started_at', 'heartbeat_at', 'lease_expires_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'item_id' => 'integer',
            'test_id' => 'integer',
            'attempt_number' => 'integer',
            'state' => AttemptState::class,
            'infrastructure_attempts' => 'integer',
            'executor_entered' => 'boolean',
            'retryable' => 'boolean',
            'permanent' => 'boolean',
            'admin_action_required' => 'boolean',
            'queued_at' => 'immutable_datetime',
            'started_at' => 'immutable_datetime',
            'heartbeat_at' => 'immutable_datetime',
            'lease_expires_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AcceptanceBatchItem::class, 'item_id');
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(AcceptanceExecutionOperation::class, 'operation_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(AcceptanceEvidence::class, 'attempt_id');
    }

    protected static function newFactory(): AcceptanceExecutionAttemptFactory
    {
        return AcceptanceExecutionAttemptFactory::new();
    }
}
