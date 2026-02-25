<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_product_code',
        'product_name',
        'product_id',
        'distributor_id',
        'stock',
        'batch_no',
        'expiry_date',
    ];

    // Optional relationships if needed later
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }
}
