<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\PermissionGroup;
use App\Models\PermissionCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $customOrder = ['superadmin', 'admin', 'distributor', 'salesmanager', 'fieldstaff', 'retailer'];

        $roles = $roles->sortBy(function ($role) use ($customOrder) {
            $index = array_search($role->name, $customOrder);
            return $index === false ? count($customOrder) : $index;
        });

        return view('admin.permissions.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $permissionGroups = PermissionGroup::with('permissionCategories')->get();
        // $actions = ['view', 'add', 'edit', 'delete'];

        $currentPermissions = DB::table('roles_permissions')
            ->where('role_id', $role->id)
            ->get()
            ->keyBy('permission_category_id');

        $groupedPermissions = [];
        foreach ($permissionGroups as $group) {
            $cats = [];
            foreach ($group->permissionCategories as $category) {
                $curr = $currentPermissions->get($category->id);
                // logic for disabled checkboxes (superadmin, admin restrictions)
                $isDisabled = false;
                if ($role->name === 'superadmin') $isDisabled = true;
                if ($role->name === 'admin' && !Auth::user()->hasRole('superadmin')) $isDisabled = true;
                if ($category->short_code === 'permissions' && !Auth::user()->hasRole('superadmin')) $isDisabled = true;

                $cats[$category->name] = [
                    'id' => $category->id,
                    'is_disabled' => $isDisabled,
                    'can_view' => $curr ? (bool)$curr->can_view : false,
                    'can_add' => $curr ? (bool)$curr->can_add : false,
                    'can_edit' => $curr ? (bool)$curr->can_edit : false,
                    'can_delete' => $curr ? (bool)$curr->can_delete : false,
                ];
            }
            $groupedPermissions[$group->name] = ['categories' => $cats];
        }

        return view('admin.permissions.edit', compact('role', 'groupedPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate(['permissions' => 'array']);

        try {
            DB::beginTransaction();
            DB::table('roles_permissions')->where('role_id', $role->id)->delete();

            $categories = PermissionCategory::all();
            foreach ($categories as $cat) {
                $p = $request->input("permissions.{$cat->id}", []);
                $v = isset($p['can_view']);
                $a = isset($p['can_add']);
                $e = isset($p['can_edit']);
                $d = isset($p['can_delete']);

                if ($v || $a || $e || $d) {
                    DB::table('roles_permissions')->insert([
                        'role_id' => $role->id,
                        'permission_category_id' => $cat->id,
                        'can_view' => $v,
                        'can_add' => $a,
                        'can_edit' => $e,
                        'can_delete' => $d,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('admin.permissions.index')->with('success', 'Permissions updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function updateSingle(Request $request, Role $role)
    {
        $request->validate([
            'category_id' => 'required|exists:permission_categories,id',
            'permission_type' => 'required|in:can_view,can_add,can_edit,can_delete',
            'value' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            // Check if superadmin is being edited by a simple admin
            if ($role->name === 'superadmin' && !Auth::user()->hasRole('superadmin')) {
                return response()->json(['error' => 'No permission to edit superadmin'], 403);
            }

            $current = DB::table('roles_permissions')
                ->where('role_id', $role->id)
                ->where('permission_category_id', $request->category_id)
                ->first();

            if ($current) {
                DB::table('roles_permissions')
                    ->where('role_id', $role->id)
                    ->where('permission_category_id', $request->category_id)
                    ->update([
                        $request->permission_type => $request->value,
                        'updated_at' => now()
                    ]);
            } else {
                $data = [
                    'role_id' => $role->id,
                    'permission_category_id' => $request->category_id,
                    'can_view' => false,
                    'can_add' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $data[$request->permission_type] = $request->value;
                DB::table('roles_permissions')->insert($data);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Permission updated.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
