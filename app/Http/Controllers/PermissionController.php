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
        $permissionGroups = PermissionGroup::with('permissionCategories')->get();
        $assignedCategoryIds = DB::table('roles_permissions')
                                 ->where('role_id', $role->id)
                                 ->pluck('permission_category_id')
                                 ->toArray();

        return view('admin.permissions.edit_role_permissions', compact('role', 'permissionGroups', 'assignedCategoryIds'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'permission_categories' => 'nullable|array',
            'permission_categories.*' => 'exists:permission_categories,id',
        ]);

        $selectedCategoryIds = $request->input('permission_categories', []);

        // Remove existing category assignments for the role
        DB::table('roles_permissions')->where('role_id', $role->id)->delete();

        // Insert new category assignments
        $dataToInsert = [];
        foreach ($selectedCategoryIds as $categoryId) {
            $dataToInsert[] = [
                'role_id' => $role->id,
                'permission_category_id' => $categoryId,
            ];
        }
        if (!empty($dataToInsert)) {
            DB::table('roles_permissions')->insert($dataToInsert);
        }

        return redirect()->route('admin.permissions.edit', $role)->with('success', 'Permissions updated successfully.');
    }
}