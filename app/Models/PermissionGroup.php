<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_code',
        'is_active',
        'system',
    ];

    public function permissionCategories()
    {
        return $this->hasMany(PermissionCategory::class, 'perm_group_id');
    }
}