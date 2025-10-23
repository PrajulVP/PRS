<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = ['district_id','name','pincode'];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
