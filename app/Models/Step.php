<?php

namespace App\Models;

use App\TestStatusEnum;
use Database\Factories\StepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    /** @use HasFactory<StepFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'duration',
        'description',
        'data',
        'status',
        'critical',
        'error_code',
        'error_message',
    ];

    protected function casts()
    {
        return [
            'data' => 'array',
            'status' => TestStatusEnum::class,
            'critical' => 'boolean',
        ];
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
