<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable; // Added missing import

class Distributor extends Model
{
    use HasFactory, Notifiable; // Combined traits

    protected $fillable = [
        'user_id',
        'name',
        'gst',
        'drug_license_no',
        'contact_no',
        'address',
        'pincode',
        'district_id',
        'area_id',
        'sales_manager_id', // New field
        'latitude',
        'longitude',
        'outstanding_amount',
        'credit_days',
        'loyalty_points',
        'credit_balance',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fieldStaffs()
    {
        return $this->belongsToMany(FieldStaff::class, 'distributor_fieldstaff', 'distributor_id', 'fieldstaff_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function salesManager()
    {
        return $this->belongsTo(SalesManager::class);
    }

    public function distributorOrders(): HasMany
    {
        return $this->hasMany(DistributorOrder::class);
    }

    public function retailerOrders(): HasMany
    {
        return $this->hasMany(RetailerOrder::class);
    }

    public function distributorTargets(): HasMany
    {
        return $this->hasMany(DistributorTarget::class);
    }

    public function distributorBenefits(): HasMany
    {
        return $this->hasMany(DistributorBenefit::class);
    }

    public function orders(): HasMany
    {
        return $this->retailerOrders();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'inventories', 'distributor_id', 'product_id')->withPivot('stock');
    }
}
