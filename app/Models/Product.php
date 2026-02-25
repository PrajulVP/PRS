<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_code',
        'product_name',
        'generic_name',
        'pack',
        'strip_size',
        'box_size',
        'carton_size',
        'hsn_code',
        'mrp',
        'ptr',
        'pts',
        'taxable_value',
        'gst',
        'offer',
        'discount',
        'net_amount',
        'loyalty_point_percentage',
    ];

    protected $casts = [
        'mrp' => 'decimal:2',
        'ptr' => 'decimal:2',
        'pts' => 'decimal:2',
        'taxable_value' => 'decimal:2',
        'gst' => 'decimal:2',
        'offer' => 'decimal:2',
        'discount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'loyalty_point_percentage' => 'decimal:2',
    ];

    // Relation with distributors via inventories
    public function distributors()
    {
        return $this->belongsToMany(Distributor::class, 'inventories', 'product_id', 'distributor_id')
            ->withPivot('stock')
            ->withTimestamps();
    }
}
