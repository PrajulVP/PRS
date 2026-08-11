<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflineLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_time',
        'to_time',
        'latitude',
        'longitude',
        'reason',
    ];

    protected $casts = [
        'from_time' => 'datetime',
        'to_time' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
