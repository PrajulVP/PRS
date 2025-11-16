<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\PermissionGroup;
use App\Models\PermissionCategory;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('admin.permissions.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $permissionGroups = PermissionGroup::with('permissionCategories')->get();
        $actions = ['view', 'add', 'edit', 'delete'];

        // Get current permissions for the role from the custom roles_permissions table
        $currentRolePermissions = DB::table('roles_permissions')
            ->where('role_id', $role->id)
            ->get()
            ->keyBy('permission_category_id');

        $groupedPermissions = [];
        foreach ($permissionGroups as $group) {
            $groupedPermissions[$group->name] = [
                'id' => $group->id,
                'categories' => []
            ];
            foreach ($group->permissionCategories as $category) {
                $currentPerms = $currentRolePermissions->get($category->id);

                $groupedPermissions[$group->name]['categories'][$category->name] = [
                    'id' => $category->id,
                    'short_code' => $category->short_code,
                    'can_view' => $currentPerms ? (bool) $currentPerms->can_view : false,
                    'can_add' => $currentPerms ? (bool) $currentPerms->can_add : false,
                    'can_edit' => $currentPerms ? (bool) $currentPerms->can_edit : false,
                    'can_delete' => $currentPerms ? (bool) $currentPerms->can_delete : false,
                ];
            }
        }

        return view('admin.permissions.edit_role_permissions', compact('role', 'groupedPermissions', 'actions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
        ]);

        // Clear existing permissions for this role in the custom table
        DB::table('roles_permissions')->where('role_id', $role->id)->delete();

        $permissionCategories = PermissionCategory::all();

        foreach ($permissionCategories as $category) {
            $categoryId = $category->id;
            $canView = $request->has("permissions.{$categoryId}.can_view");
            $canAdd = $request->has("permissions.{$categoryId}.can_add");
            $canEdit = $request->has("permissions.{$categoryId}.can_edit");
            $canDelete = $request->has("permissions.{$categoryId}.can_delete");

            // Only insert if at least one permission is granted for the category
            if ($canView || $canAdd || $canEdit || $canDelete) {
                DB::table('roles_permissions')->insert([
                    'role_id' => $role->id,
                    'permission_category_id' => $categoryId,
                    'can_view' => $canView,
                    'can_add' => $canAdd,
                    'can_edit' => $canEdit,
                    'can_delete' => $canDelete,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.permissions.edit', $role)->with('success', 'Permissions updated successfully.');
    }
}
