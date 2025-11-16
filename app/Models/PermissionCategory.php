<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PermissionCategory extends Model
{
    protected $fillable = [
        'perm_group_id',
        'name',
        'short_code',
        'enable_view',
        'enable_add',
        'enable_edit',
        'enable_delete',
    ];

    public function permissionGroup()
    {
        return $this->belongsTo(PermissionGroup::class, 'perm_group_id');
    }
}
