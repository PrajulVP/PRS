<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailerOrder extends Model
{
    protected $table = 'retailer_orders';

    protected $fillable = [
        'distributor_id',
        'retailer_id',
        'product_name',
        'sku',
        'quantity',
        'unit_price',
        'total_amount',
        'status',
        'placed_at',
        'notes',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
    ];

    public function retailer(): BelongsTo
    {
        return $this->belongsTo(Retailer::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }
}
