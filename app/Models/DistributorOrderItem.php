<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class distributorOrderItem extends Model
{
    protected $fillable = [
        'distributor_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_amount',
    ];

    public function distributorOrder(): BelongsTo
    {
        return $this->belongsTo(distributorOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
