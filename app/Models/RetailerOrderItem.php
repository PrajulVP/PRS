<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailerOrderItem extends Model
{
    protected $fillable = [
        'retailer_order_id',
        'product_id',
        'product_name',
        'quantity',
        'free_quantity',
        'unit',
        'unit_price',
        'total_amount',
        'side',
        'size',
        'free_product_id',
        'free_side',
        'free_size',
    ];

    public function retailerOrder(): BelongsTo
    {
        return $this->belongsTo(RetailerOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function freeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'free_product_id');
    }

    public function batches()
    {
        return $this->hasMany(RetailerOrderItemBatch::class, 'retailer_order_item_id');
    }

    public function getIsFreeAttribute()
    {
        return (float)$this->unit_price == 0;
    }
}
