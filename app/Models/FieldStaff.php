<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}