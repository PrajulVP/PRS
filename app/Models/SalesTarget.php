<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTarget extends Model
{
    protected $fillable = [
        'field_staff_id',
        'distributor_id',
        'month',
        'year',
        'brand',
        'amount',
        'achieved_amount',
    ];

    public function fieldStaff(): BelongsTo
    {
        return $this->belongsTo(FieldStaff::class);
    }
}
