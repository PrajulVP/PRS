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
    ];
}
