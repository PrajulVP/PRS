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
    ];

    protected $casts = [
        'custom_fields' => 'array',
    ];
}
