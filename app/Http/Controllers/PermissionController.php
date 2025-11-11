<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\Permission; // Use our extended Permission model
use App\Models\PermissionGroup;
use App\Models\PermissionCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('admin.permissions.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $permissionGroups = PermissionGroup::with('permissionCategories.permissions')->get();
        $actions = ['view', 'add', 'edit', 'delete']; // Define the actions

        // Prepare data for the view
        $groupedPermissions = [];
        foreach ($permissionGroups as $group) {
            $groupedPermissions[$group->name] = [
                'id' => $group->id,
                'categories' => []
            ];
            foreach ($group->permissionCategories as $category) {
                $groupedPermissions[$group->name]['categories'][$category->name] = [
                    'id' => $category->id,
                    'short_code' => $category->short_code,
                    'enable_view' => $category->enable_view,
                    'enable_add' => $category->enable_add,
                    'enable_edit' => $category->enable_edit,
                    'enable_delete' => $category->enable_delete,
                    'permissions' => []
                ];
                foreach ($category->permissions as $permission) {
                    // Extract action from permission name (e.g., "view users" -> "view")
                    $action = explode(' ', $permission->name)[0];
                    $groupedPermissions[$group->name]['categories'][$category->name]['permissions'][$action] = $permission;
                }
            }
        }

        return view('admin.permissions.edit_role_permissions', compact('role', 'groupedPermissions', 'actions'));
    }

    public function update(Request $request, Role $role)
    {
        $inputPermissions = $request->input('permissions', []); // This will be an array of permission IDs that were checked

        // Get all permission IDs from the request that are checked
        $checkedPermissionIds = array_keys($inputPermissions);

        // Get the actual Permission models for the checked IDs
        $permissionsToSync = Permission::whereIn('id', $checkedPermissionIds)->get();

        // Sync the permissions for the role
        $role->syncPermissions($permissionsToSync);

        return redirect()->route('admin.permissions.edit', $role)->with('success', 'Permissions updated successfully.');
    }
}