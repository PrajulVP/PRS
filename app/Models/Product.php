<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'product_name',
        'generic_name',
        'pack_quantity',
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
        'stock', // Added stock to fillable
    ];

    protected $casts = [
        'expiry' => 'date',
    ];

    public function distributors()
    {
<<<<<<< HEAD
        return $this->belongsToMany(Distributor::class)->withPivot('stock');
=======
        return $this->belongsToMany(Distributor::class, 'distributor_product')->withPivot('stock');
>>>>>>> 91090156be59a846bc1e79fcc62d6a0abcb78dc0
    }
}
