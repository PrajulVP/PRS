<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    protected $fillable = [
        'inventory_id',
        'user_id',
        'previous_stock',
        'new_stock',
        'quantity_change',
        'change_type',
        'remarks',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
