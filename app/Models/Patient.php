<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'retailer_id',
        'name',
        'contact',
        'medication_history',
        'category',
        'next_reorder_date'
    ];

    protected $casts = [
        'next_reorder_date' => 'date',
    ];

    public function retailer()
    {
        return $this->belongsTo(Retailer::class);
    }
}
