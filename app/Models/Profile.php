<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'secret',
        'data'
    ];

    protected function casts()
    {
        return [
            'data' => 'array',
            'secret' => 'hashed'
        ];
    }
}
