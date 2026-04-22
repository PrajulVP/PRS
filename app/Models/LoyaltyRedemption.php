<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoyaltyRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'retailer_id',
        'distributor_id',
        'loyalty_slab_id',
        'status',
        'expected_delivery_date',
        'gift_receipt_photo',
        'admin_notes'
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
    ];

    public function retailer()
    {
        return $this->belongsTo(Retailer::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function slab()
    {
        return $this->belongsTo(LoyaltySlab::class, 'loyalty_slab_id');
    }
}
