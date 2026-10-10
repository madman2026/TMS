<?php

namespace App\Models;

use App\Acceptance\Reporting\Enums\EvidenceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AcceptanceEvidence extends Model
{
    protected $table = 'acceptance_evidence';

    protected $fillable = [
        'attempt_id', 'type', 'reference_key', 'checksum_sha256', 'size_bytes', 'media_type',
        'width', 'height', 'duration_ms', 'captured_at', 'available_until',
    ];

    protected function casts(): array
    {
        return [
            'attempt_id' => 'integer',
            'type' => EvidenceType::class,
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration_ms' => 'integer',
            'captured_at' => 'immutable_datetime',
            'available_until' => 'immutable_datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(AcceptanceExecutionAttempt::class, 'attempt_id');
    }
}
