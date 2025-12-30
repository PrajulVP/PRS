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

    public function products()
    {
        return $this->belongsToMany(Product::class, 'distributor_product')->withPivot('stock');
    }
}
