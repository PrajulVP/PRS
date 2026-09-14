<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HospitalClinic extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hospitals_clinics';

    protected $fillable = [
        'name',
        'address',
        'district_id',
        'area_id',
        'latitude',
        'longitude',
        'location_locked',
        'location_updated_at',
        'status',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'location_locked' => 'boolean',
        'location_updated_at' => 'datetime',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
