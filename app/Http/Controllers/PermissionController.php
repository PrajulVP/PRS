<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
<<<<<<< HEAD
use App\Models\Role;
use App\Models\Permission; // Use our extended Permission model
=======
use Spatie\Permission\Models\Role;
>>>>>>> 91090156be59a846bc1e79fcc62d6a0abcb78dc0
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
<<<<<<< HEAD
        // Define the desired order of permission groups and categories
        $groupOrder = [
            'Users',
            'Regions & Areas',
            'Products',
            'Orders',
        ];

        $categoryOrder = [
            'Users' => ['Manage Permissions', 'Managers', 'Distributors', 'Field Staff', 'Retailers'],
            'Regions & Areas' => ['Districts', 'Areas'],
            'Products' => ['Products'],
            'Orders' => ['Retailer Orders', 'Distributor Bulk Orders'],
        ];

        $permissionGroups = PermissionGroup::with(['permissionCategories' => function ($query) use ($categoryOrder) {
            $query->orderByRaw(DB::raw('FIELD(name, "' . implode('","', $categoryOrder['Users']) . '", "' . implode('","', $categoryOrder['Regions & Areas']) . '", "' . implode('","', $categoryOrder['Products']) . '", "' . implode('","', $categoryOrder['Orders']) . '")'));
        }, 'permissionCategories.permissions'])->get();

        // Sort permission groups based on the defined order
        $permissionGroups = $permissionGroups->sortBy(function ($group) use ($groupOrder) {
            return array_search($group->name, $groupOrder);
        });

        $actions = ['view', 'add', 'edit', 'delete']; // Define the actions
=======
        $permissionGroups = PermissionGroup::with('permissionCategories')->get();
        $actions = ['view', 'add', 'edit', 'delete'];

        // Get current permissions for the role from the custom roles_permissions table
        $currentRolePermissions = DB::table('roles_permissions')
            ->where('role_id', $role->id)
            ->get()
            ->keyBy('permission_category_id');
>>>>>>> 91090156be59a846bc1e79fcc62d6a0abcb78dc0

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
<<<<<<< HEAD
                foreach ($category->permissions as $permission) {
                    $action = '';
                    if (Str::startsWith($permission->name, 'view ')) {
                        $action = 'view';
                    } elseif (Str::startsWith($permission->name, 'add ') || Str::startsWith($permission->name, 'create ')) {
                        $action = 'add';
                    } elseif (Str::startsWith($permission->name, 'edit ')) {
                        $action = 'edit';
                    } elseif (Str::startsWith($permission->name, 'delete ')) {
                        $action = 'delete';
                    } elseif ($permission->name === 'view my orders') {
                        $action = 'view';
                    } elseif (Str::startsWith($permission->name, 'assign_distributor ')) {
                        $action = 'edit'; // Assigning is an edit action
                    } elseif (Str::startsWith($permission->name, 'assign_fieldstaff ')) {
                        $action = 'edit'; // Assigning is an edit action
                    } elseif (Str::startsWith($permission->name, 'update_delivery_status ')) {
                        $action = 'edit'; // Updating status is an edit action
                    }

                    if ($action) {
                        $groupedPermissions[$group->name]['categories'][$category->name]['permissions'][$action] = $permission;
                    }
                }
=======
>>>>>>> 91090156be59a846bc1e79fcc62d6a0abcb78dc0
            }
        }

        $assignedPermissions = DB::table('roles_permissions')
            ->where('role_id', $role->id)
            ->get()
            ->keyBy('permission_category_id');

        return view('admin.permissions.edit_role_permissions', compact('role', 'groupedPermissions', 'actions', 'assignedPermissions'));
    }

    public function update(Request $request, Role $role)
    {
<<<<<<< HEAD
        $inputPermissions = $request->input('permissions', []);
        \Illuminate\Support\Facades\Log::info('Input Permissions: ' . json_encode($inputPermissions));
        $checkedPermissionIds = array_keys($inputPermissions);
        \Illuminate\Support\Facades\Log::info('Checked Permission IDs: ' . json_encode($checkedPermissionIds));

        $allPermissions = Permission::with('permissionCategory')->get();
        $permissionMap = $allPermissions->keyBy('id');

        $rolePermissionsData = []; // To store data for roles_permissions table

        foreach ($checkedPermissionIds as $permissionId) {
            $permission = $permissionMap->get($permissionId);

            if ($permission && $permission->permissionCategory) {
                $categoryId = $permission->permissionCategory->id;
                $action = '';
                if (Str::startsWith($permission->name, 'view ')) {
                    $action = 'view';
                } elseif (Str::startsWith($permission->name, 'add ') || Str::startsWith($permission->name, 'create ')) {
                    $action = 'add';
                } elseif (Str::startsWith($permission->name, 'edit ')) {
                    $action = 'edit';
                } elseif (Str::startsWith($permission->name, 'delete ')) {
                    $action = 'delete';
                } elseif ($permission->name === 'view my orders') {
                    $action = 'view';
                } elseif (Str::startsWith($permission->name, 'assign_distributor ')) {
                    $action = 'edit'; // Assigning is an edit action
                } elseif (Str::startsWith($permission->name, 'assign_fieldstaff ')) {
                    $action = 'edit'; // Assigning is an edit action
                } elseif (Str::startsWith($permission->name, 'update_delivery_status ')) {
                    $action = 'edit'; // Updating status is an edit action
                }

                if (!isset($rolePermissionsData[$categoryId])) {
                    $rolePermissionsData[$categoryId] = [
                        'role_id' => $role->id,
                        'permission_category_id' => $categoryId,
                        'can_view' => false,
                        'can_add' => false,
                        'can_edit' => false,
                        'can_delete' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                // Set the specific action to true
                if (in_array($action, ['view', 'add', 'edit', 'delete'])) {
                    $rolePermissionsData[$categoryId]['can_' . $action] = true;
                }
            }
        }
        \Illuminate\Support\Facades\Log::info('Role Permissions Data before DB operations: ' . json_encode($rolePermissionsData));

        // Delete existing entries for this role
        DB::table('roles_permissions')->where('role_id', $role->id)->delete();
        \Illuminate\Support\Facades\Log::info('Deleted existing roles_permissions for role_id: ' . $role->id);

        // Insert new entries
        if (!empty($rolePermissionsData)) {
            DB::table('roles_permissions')->insert(array_values($rolePermissionsData));
            \Illuminate\Support\Facades\Log::info('Inserted new roles_permissions data.');
        } else {
            \Illuminate\Support\Facades\Log::info('No new roles_permissions data to insert.');
        }
=======
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
>>>>>>> 91090156be59a846bc1e79fcc62d6a0abcb78dc0

        return redirect()->route('admin.permissions.edit', $role)->with('success', 'Permissions updated successfully.');
    }
}
