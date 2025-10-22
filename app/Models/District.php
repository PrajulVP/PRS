<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'districts';  // optional if table name is correct

    protected $fillable = [
        'name',
        'state_id',
        'status',
    ];
}
