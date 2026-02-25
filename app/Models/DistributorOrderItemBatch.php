<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorOrderItemBatch extends Model
{
    protected $fillable = [
        'distributor_order_item_id',
        'batch_no',
        'expiry_date',
        'quantity',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(DistributorOrderItem::class, 'distributor_order_item_id');
    }
}
