<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'retailer_id',
        'raw_text',
        'extracted_data',
    ];

    protected $casts = [
        'extracted_data' => 'array',
    ];

    public function retailer()
    {
        return $this->belongsTo(Retailer::class);
    }
}
