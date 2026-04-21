<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncentiveSlab extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_achievement_percent',
        'max_achievement_percent',
        'incentive_percent',
        'is_active',
    ];
}
