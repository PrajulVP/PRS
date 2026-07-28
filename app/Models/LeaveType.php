<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'default_quota',
    ];

    public function balances()
    {
        return $this->hasMany(UserLeaveBalance::class);
    }
}
