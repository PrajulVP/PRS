<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailerOrderItemBatch extends Model
{
    protected $fillable = [
        'retailer_order_item_id',
        'batch_no',
        'expiry_date',
        'quantity',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(RetailerOrderItem::class, 'retailer_order_item_id');
    }
}
