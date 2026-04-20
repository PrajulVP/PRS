<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_category',
        'customer_name',
        'customer_id',
        'latitude',
        'longitude',
        'check_in_at',
        'check_out_at',
        'notes',
        'next_follow_up_date',
        'photo_path',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'next_follow_up_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
