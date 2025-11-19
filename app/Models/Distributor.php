<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distributor extends Model
{
    protected $fillable = [
        'user_id',
        'gst',
        'drug_license_number',
        'contact_no',
        'address',
        'pincode',
        'district_id',
        'area_id',
        'route',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function distributorOrders(): HasMany
    {
        return $this->hasMany(DistributorOrder::class);
    }

    public function retailerOrders(): HasMany
    {
        return $this->hasMany(RetailerOrder::class);
    }

<<<<<<< HEAD
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('stock');
=======
    public function fieldStaffs(): HasMany
    {
        return $this->hasMany(FieldStaff::class, 'assigned_distributor_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'distributor_product')->withPivot('stock');
>>>>>>> 91090156be59a846bc1e79fcc62d6a0abcb78dc0
    }
}