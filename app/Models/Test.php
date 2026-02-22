<?php

namespace App\Models;

use App\TestStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    /** @use HasFactory<\Database\Factories\TestFactory> */
    use HasFactory;

    protected $fillable = [
        'duration',
        'name',
        'status',
        'data'
    ];

    protected function casts()
    {
        return [
            'status' => TestStatusEnum::class,
            'data' => 'array'
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
