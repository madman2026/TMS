<?php

namespace App\Models;

use App\DeviceTypeEnum;
use App\DriverTypeEnum;
use App\InternetSpeedEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'extra',
        'device',
        'driver',
        'internet_speed'
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

    public function tests()
    {
        return $this->hasMany(Test::class);
    }
}
