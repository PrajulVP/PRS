<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'contact_no',
        'address',
        'pincode',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fieldStaffs()
    {
        return $this->hasMany(FieldStaff::class);
    }

    public function retailers()
    {
        return $this->hasMany(Retailer::class);
    }
}
