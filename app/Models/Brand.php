<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'layout_type',
        'custom_fields',
        'is_returnable',
        'is_loyalty_enabled',
    ];

    protected $casts = [
        'custom_fields' => 'array',
    ];
}
