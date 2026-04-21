<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoyaltySlab extends Model
{
    use HasFactory;

    protected $fillable = [
        'slab_name',
        'min_points',
        'gift_name',
        'gift_image',
        'description'
    ];

    public function redemptions()
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }
}
