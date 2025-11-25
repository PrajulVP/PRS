<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str; // Import Str facade

class RetailerOrder extends Model
{
    use HasFactory;
    
    protected $table = 'retailer_orders';

    protected $fillable = [
        'order_code',
        'distributor_id',
        'retailer_id',
        'total_amount',
        'total_items',
        'total_quantity',
        'status',
        'placed_at',
        'notes',
        'delivered_at',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            do {
                $orderCode = 'RO-' . Str::upper(Str::random(6)); // Example: RO-X9Y8Z7
            } while (RetailerOrder::where('order_code', $orderCode)->exists());
            $order->order_code = $orderCode;
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(RetailerOrderItem::class);
    }

    public function retailer(): BelongsTo
    {
        return $this->belongsTo(Retailer::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }
}