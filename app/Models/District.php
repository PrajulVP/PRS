<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function distributors(): HasMany
    {
        return $this->hasMany(Distributor::class);
    }
}
