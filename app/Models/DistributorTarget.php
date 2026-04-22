<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributorTarget extends Model
{
    protected $fillable = [
        'distributor_id',
        'month',
        'year',
        'target_amount',
        'achieved_amount',
    ];

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }
}
