<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'product_name',
        'generic_name',
        'pack',           
        'quantity',      
        'expiry',
        'strip_size',
        'box_size',
        'carton_size',
        'hsn_code',
        'batch_no',
        'mrp',
        'ptr',
        'taxable_value',
        'gst',
        'offer',
        'discount',
        'net_amount',
        'stock',         
    ];

    protected $casts = [
        'expiry' => 'date',
        'mrp' => 'decimal:2',
        'ptr' => 'decimal:2',
        'taxable_value' => 'decimal:2',
        'gst' => 'decimal:2',
        'offer' => 'decimal:2',
        'discount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    // Relation with distributors
    public function distributors()
    {
        return $this->belongsToMany(Distributor::class, 'distributor_product')
            ->withPivot('stock')
            ->withTimestamps();
    }
}
