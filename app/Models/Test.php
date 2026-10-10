<?php

namespace App\Models;

use App\TestStatusEnum;
use Database\Factories\TestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Test extends Model
{
    /** @use HasFactory<TestFactory> */
    use HasFactory;

    protected $fillable = [
        'duration',
        'name',
        'status',
        'data',
        'app_key',
        'component_key',
        'suite_key',
        'scenario_key',
        'variant_key',
        'error_code',
    ];

    protected function casts()
    {
        return [
            'status' => TestStatusEnum::class,
            'data' => 'array',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function steps()
    {
        return $this->hasMany(Step::class);
    }

    public function acceptanceExecutionAttempt(): HasOne
    {
        return $this->hasOne(AcceptanceExecutionAttempt::class);
    }

    public function acceptanceEvidence(): HasManyThrough
    {
        return $this->hasManyThrough(
            AcceptanceEvidence::class,
            AcceptanceExecutionAttempt::class,
            'test_id',
            'attempt_id',
            'id',
            'id',
        );
    }
}
