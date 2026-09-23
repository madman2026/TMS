<?php

namespace App\Models;

use App\TestStatusEnum;
use Database\Factories\TestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'scenario_key',
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
}
