<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retailer extends Model
{
    protected $table = 'retailers';
    protected $fillable = [
        'name',
        'gst',
        'contact_no',
        'email',
        'password',
        'district_id',
        'area_id',
        'distributor_id',
        'route',
        'address',
        'pincode',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }


    public function distributor()
    {
        return $this->belongsTo(Distributor::class, 'distributor_id');
    }

}
