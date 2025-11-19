<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str; // Import Str facade

class DistributorOrder extends Model
{
    protected $table = 'distributor_orders';

    protected $fillable = [
        'order_code', // Added
        'product_name',
        'sku',
        'quantity',
        'unit_price',
        'total_amount',
        'status',
        'placed_at',
        'notes',
        'prescription_photo',
        'delivery_notes',
        'distributor_id',
        'fieldstaff_id',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
        'approved_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            do {
                $orderCode = 'DO-' . Str::upper(Str::random(6)); // Example: DO-A1B2C3
            } while (DistributorOrder::where('order_code', $orderCode)->exists());
            $order->order_code = $orderCode;
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function fieldstaff(): BelongsTo
    {
        return $this->belongsTo(FieldStaff::class);
    }
}