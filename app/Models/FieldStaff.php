<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldStaff extends Model
{
    protected $table = 'fieldstaffs';
    protected $fillable = [
        'user_id',
        'assigned_distributor_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function distributorOrders(): HasMany
    {
        return $this->hasMany(DistributorOrder::class);
    }

    public function retailers(): HasMany
    {
        return $this->hasMany(Retailer::class);
    }

    public function salesTargets(): HasMany
    {
        return $this->hasMany(SalesTarget::class);
    }
}