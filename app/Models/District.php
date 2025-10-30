<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Model;


class District extends Model
{
    protected $table = 'districts';  // optional if table name is correct

    protected $fillable = [
        'name',
        'code'
    ];

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function distributors(): HasMany
    {
        return $this->hasMany(Distributor::class);
    }
}
