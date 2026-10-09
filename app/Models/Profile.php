<?php

namespace App\Models;

use App\DeviceTypeEnum;
use App\DriverTypeEnum;
use App\InternetSpeedEnum;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'extra',
        'device',
        'driver',
        'internet_speed',
    ];

    protected function casts()
    {
        return [
            'extra' => 'array',
            'driver' => DriverTypeEnum::class,
            'device' => DeviceTypeEnum::class,
            'internet_speed' => InternetSpeedEnum::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tests()
    {
        return $this->hasMany(Test::class);
    }

    public function acceptanceBatches(): HasMany
    {
        return $this->hasMany(AcceptanceBatch::class);
    }
}
