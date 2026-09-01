<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoyaltySlab extends Model
{
    use HasFactory;

    protected $fillable = [
        'slab_name',
        'brand_id',
        'min_points',
        'gift_name',
        'gift_image',
        'description',
        'is_active',
        'reward_options'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    public function redemptions()
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }
    
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
