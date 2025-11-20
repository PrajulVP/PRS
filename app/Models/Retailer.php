<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Retailer extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'distributor_id',
        'field_staff_id',
        'district_id',
        'area_id',
        'proprietor_name',
        'contact_no',
        'gst',
        'address',
        'status',
        'credit_limit',
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

    public function retailerOrders(): HasMany
    {
        return $this->hasMany(RetailerOrder::class);
    }

    public function fieldStaff()
    {
        return $this->belongsTo(FieldStaff::class);
    }
}