<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorOrderItem extends Model
{
    protected $fillable = [
        'distributor_order_id',
        'product_id',
        'product_name',
        'quantity',
        'free_quantity',
        'unit',
        'price',
        'subtotal',
        'side',
        'size',
        'free_product_id',
        'free_side',
        'free_size',
    ];

    public function distributorOrder(): BelongsTo
    {
        return $this->belongsTo(DistributorOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batches()
    {
        return $this->hasMany(DistributorOrderItemBatch::class, 'distributor_order_item_id');
    }
}
