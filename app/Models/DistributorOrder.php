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
    const STATUS_PROCESSING = 'processing';
    const STATUS_APPROVED = 'approved';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'order_code',
        'total_amount',
        'total_items',
        'total_quantity',
        'status',
        'placed_at',
        'delivery_notes',
        'distributor_id',
        'sales_manager_id', // New field
        'cancellation_reason', // New field
        'payment_status', // New field
        'invoice_path',
        'invoice_no',
        'metadata',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $month = (int)date('n');
            $year = (int)date('y');
            if ($month < 4) {
                $year = $year - 1;
            }
            $prefix = 'DO-' . str_pad($year, 2, '0', STR_PAD_LEFT);

            $latestOrder = static::where('order_code', 'like', $prefix . '%')
                ->orderByRaw('LENGTH(order_code) DESC')
                ->orderBy('order_code', 'desc')
                ->first();

            $sequence = 0;
            if ($latestOrder) {
                $sequence = (int)substr($latestOrder->order_code, 5);
            }

            do {
                $sequence++;
                $orderCode = $prefix . str_pad($sequence, 5, '0', STR_PAD_LEFT);
            } while (static::where('order_code', $orderCode)->exists());

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

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class, 'order_id')->where('order_type', 'distributor');
    }
}
