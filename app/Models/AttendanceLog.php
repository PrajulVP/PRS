<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'latitude',
        'longitude',
        'device_id',
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
