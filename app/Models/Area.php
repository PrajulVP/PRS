<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends Model
{
    use HasFactory;
    protected $fillable = ['district_id','name', 'pincode'];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function retailers()
    {
        return $this->hasMany(Retailer::class);
    }
}
