<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'retailer_id',
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

    protected $dates = ['placed_at','approved_at','delivered_at'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function retailer(): BelongsTo
    {
        return $this->belongsTo(Retailer::class);
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
