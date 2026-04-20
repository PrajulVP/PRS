<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LocationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'is_mock_location',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'is_mock_location' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
