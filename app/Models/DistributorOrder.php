<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str; // Import Str facade

class DistributorOrder extends Model
{
    protected $table = 'distributor_orders';

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED_BY_SALES_MANAGER = 'accepted_by_sales_manager';
    const STATUS_DELIVERED = 'delivered'; // Admin accepted
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLATION_REQUESTED = 'cancellation_requested';

    protected $fillable = [
        'order_code',
        'total_amount',
        'total_items',
        'total_quantity',
        'status',
        'placed_at',
        'notes',
        'delivery_notes',
        'distributor_id',
        'sales_manager_id', // New field
        'cancellation_reason', // New field
        'payment_status', // New field
        'invoice_path',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            do {
                $orderCode = 'DO-' . Str::upper(Str::random(6)); // Example: DO-A1B2C3
            } while (DistributorOrder::where('order_code', $orderCode)->exists());
            $order->order_code = $orderCode;
            $order->status = self::STATUS_PENDING; // Ensure default status
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(DistributorOrderItem::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function salesManager(): BelongsTo
    {
        return $this->belongsTo(SalesManager::class, 'sales_manager_id');
    }
}
