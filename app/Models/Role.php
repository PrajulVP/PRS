<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Support\Facades\DB;

class Role extends SpatieRole
{
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        // Superadmin always has all permissions
        if ($this->name === 'superadmin') {
            return true;
        }

        // If the permission is a string, find the Permission model directly
        if (is_string($permission)) {
            \Illuminate\Support\Facades\Log::info('Checking permission: ' . $permission . ' for guard: ' . $guardName);
            $permissionModel = \App\Models\Permission::where('name', $permission)
                                                    ->where('guard_name', $guardName)
                                                    ->first();
            \Illuminate\Support\Facades\Log::info('Direct query returned: ' . ($permissionModel ? $permissionModel->name : 'NULL'));
            $permission = $permissionModel;
        }

        // If permission not found, or no category, return false
        if (!$permission || !$permission->permissionCategory) {
            return false;
        }

        // Determine the action from the permission name (e.g., "view users" -> "view")
        $action = explode(' ', $permission->name)[0];

        \Illuminate\Support\Facades\Log::info('Role hasPermissionTo check:');
        \Illuminate\Support\Facades\Log::info('  Role ID: ' . $this->id);
        \Illuminate\Support\Facades\Log::info('  Permission Name: ' . $permission->name);
        \Illuminate\Support\Facades\Log::info('  Permission Category ID: ' . $permission->permissionCategory->id);
        \Illuminate\Support\Facades\Log::info('  Extracted Action: ' . $action);
        \Illuminate\Support\Facades\Log::info('  Checking can_' . $action . ' for category ' . $permission->permissionCategory->id);

        // Check if this role is linked to the permission's category in the custom roles_permissions table
        // and if the specific action (can_view, can_add, etc.) is true.
        $result = DB::table('roles_permissions')
            ->where('role_id', $this->id)
            ->where('permission_category_id', $permission->permissionCategory->id)
            ->where('can_' . $action, true)
            ->exists();
        
        \Illuminate\Support\Facades\Log::info('  DB Query Result: ' . ($result ? 'TRUE' : 'FALSE'));
        return $result;
    }
}
