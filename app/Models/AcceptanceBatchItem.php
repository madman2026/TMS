<?php

namespace App\Models;

use App\Acceptance\Execution\Enums\BatchItemState;
use Database\Factories\AcceptanceBatchItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AcceptanceBatchItem extends Model
{
    /** @use HasFactory<AcceptanceBatchItemFactory> */
    use HasFactory;

    protected $attributes = [
        'state' => BatchItemState::PENDING->value,
        'lock_version' => 0,
        'attempt_count' => 0,
        'admin_action_required' => false,
    ];

    protected $fillable = [
        'batch_id', 'acceptance_operation_request_id', 'retry_of_item_id', 'ordinal', 'app_key',
        'component_key', 'suite_key', 'scenario_key', 'variant_key', 'capability',
        'catalog_version', 'classification_snapshot', 'idempotency_key', 'state', 'lock_version',
        'attempt_count', 'error_code', 'cleanup_error_code', 'retryable', 'permanent',
        'admin_action_required', 'queued_at', 'started_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'batch_id' => 'integer',
            'retry_of_item_id' => 'integer',
            'ordinal' => 'integer',
            'classification_snapshot' => 'array',
            'state' => BatchItemState::class,
            'lock_version' => 'integer',
            'attempt_count' => 'integer',
            'retryable' => 'boolean',
            'permanent' => 'boolean',
            'admin_action_required' => 'boolean',
            'queued_at' => 'immutable_datetime',
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(AcceptanceBatch::class, 'batch_id');
    }

    public function prerequisiteRequest(): BelongsTo
    {
        return $this->belongsTo(AcceptanceOperationRequest::class, 'acceptance_operation_request_id');
    }

    public function retrySource(): BelongsTo
    {
        return $this->belongsTo(self::class, 'retry_of_item_id');
    }

    public function retries(): HasMany
    {
        return $this->hasMany(self::class, 'retry_of_item_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AcceptanceExecutionAttempt::class, 'item_id');
    }

    protected static function newFactory(): AcceptanceBatchItemFactory
    {
        return AcceptanceBatchItemFactory::new();
    }
}
