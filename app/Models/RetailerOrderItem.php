<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailerOrderItem extends Model
{
    protected $fillable = [
        'retailer_order_id',
        'product_id',
        'quantity',
        'unit',
        'unit_price',
        'total_amount',
    ];

    public function retailerOrder(): BelongsTo
    {
        return $this->belongsTo(RetailerOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batches()
    {
        return $this->hasMany(RetailerOrderItemBatch::class, 'retailer_order_item_id');
    }
}
