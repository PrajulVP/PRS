<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class FieldStaff extends Model
{
    protected $table = 'fieldstaffs';


    protected $fillable = [
        'name',
        'email',
        'password',
        'contact_no',
        'district_id',
        'area_id',
        'assigned_distributor_id',
        'address',
        'status',
    ];


    protected $hidden = [
        'password',
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
        return $this->belongsTo(Distributor::class, 'assigned_distributor_id');
    }
}