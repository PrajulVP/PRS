<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorOrderItem extends Model
{
    protected $fillable = [
        'distributor_order_id',
        'product_id',
        'quantity',
        'unit',
        'price',
        'subtotal',
    ];

    public function distributorOrder(): BelongsTo
    {
        return $this->belongsTo(DistributorOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
