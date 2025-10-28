<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
     protected $fillable = [
        'company_name','gst','regulations','contact_no','address',
        'pincode','district_id','area_id','route','email','password'
    ];

    public function district() { return $this->belongsTo(District::class); }
    public function area()     { return $this->belongsTo(Area::class); }
    public function chemists() { return $this->hasMany(Retailer::class);}
    public function orders() { return $this->hasMany(Order::class); }
    public function targets() { return $this->hasMany(SalesTarget::class); }

}
