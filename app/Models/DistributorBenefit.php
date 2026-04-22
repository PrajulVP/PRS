<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributorBenefit extends Model
{
    protected $fillable = [
        'target_amount',
        'reward_name',
        'reward_image',
        'description',
        'is_active',
    ];
}
