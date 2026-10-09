<?php

namespace App\Models;

use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\ExecutionMode;
use Database\Factories\AcceptanceBatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class AcceptanceBatch extends Model
{
    /** @use HasFactory<AcceptanceBatchFactory> */
    use HasFactory;

    protected $attributes = [
        'state' => BatchState::PLANNED->value,
        'lock_version' => 0,
        'matched_count' => 0,
        'executable_count' => 0,
        'skipped_count' => 0,
        'max_failures' => 0,
        'failure_count' => 0,
        'next_dispatch_ordinal' => 0,
    ];

    protected $fillable = [
        'parent_batch_id', 'profile_id', 'correlation_id', 'mode', 'version', 'plan_fingerprint',
        'selector_snapshot', 'catalog_versions', 'state', 'lock_version', 'matched_count',
        'executable_count', 'skipped_count', 'max_failures', 'failure_count',
        'next_dispatch_ordinal', 'laravel_batch_id', 'cancel_requested_at', 'started_at',
        'finished_at', 'interrupted_at', 'reconciled_at',
    ];

    protected function casts(): array
    {
        return [
            'parent_batch_id' => 'integer',
            'profile_id' => 'integer',
            'mode' => ExecutionMode::class,
            'version' => 'integer',
            'selector_snapshot' => 'array',
            'catalog_versions' => 'array',
            'state' => BatchState::class,
            'lock_version' => 'integer',
            'matched_count' => 'integer',
            'executable_count' => 'integer',
            'skipped_count' => 'integer',
            'max_failures' => 'integer',
            'failure_count' => 'integer',
            'next_dispatch_ordinal' => 'integer',
            'cancel_requested_at' => 'immutable_datetime',
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
            'interrupted_at' => 'immutable_datetime',
            'reconciled_at' => 'immutable_datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_batch_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_batch_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function operation(): HasOne
    {
        return $this->hasOne(AcceptanceExecutionOperation::class, 'batch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AcceptanceBatchItem::class, 'batch_id');
    }

    protected static function newFactory(): AcceptanceBatchFactory
    {
        return AcceptanceBatchFactory::new();
    }
}
