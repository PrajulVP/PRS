<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'retailer_id',
        'field_staff_id',
        'rating',
        'category',
        'comments',
    ];

    public function retailer()
    {
        return $this->belongsTo(Retailer::class);
    }

    public function fieldStaff()
    {
        return $this->belongsTo(FieldStaff::class);
    }
}
