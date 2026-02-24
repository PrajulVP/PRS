<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission; // Use Spatie Permission model
use App\Models\PermissionCategory;
use App\Models\PermissionGroup; // Import PermissionGroup model

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find or create the "Orders" permission group
        $ordersGroup = PermissionGroup::firstOrCreate(['name' => 'Orders']);

        // Find or create the "User Management" permission group
        $userManagementGroup = PermissionGroup::firstOrCreate(
            ['name' => 'User Management'],
            ['short_code' => 'user_management', 'is_active' => true, 'system' => true]
        );

        // Find or create the "Regions & Areas" permission group
        $regionsAreasGroup = PermissionGroup::firstOrCreate(
            ['name' => 'Regions & Areas'],
            ['short_code' => 'regions_areas', 'is_active' => true, 'system' => true]
        );

        // Find or create the "Products" permission group
        $productsGroup = PermissionGroup::firstOrCreate(
            ['name' => 'Products'],
            ['short_code' => 'products', 'is_active' => true, 'system' => true]
        );


        // Explicitly create all Permission Categories
        $categoriesToCreate = [
            // User Management Group (Users removed as requested)
            [
                'short_code' => 'distributors',
                'name' => 'Distributors',
                'perm_group_id' => $userManagementGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
            [
                'short_code' => 'sales_managers',
                'name' => 'Sales Managers',
                'perm_group_id' => $userManagementGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
            [
                'short_code' => 'field_staff',
                'name' => 'Field Staff',
                'perm_group_id' => $userManagementGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
            [
                'short_code' => 'retailers',
                'name' => 'Retailers',
                'perm_group_id' => $userManagementGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
            // Regions & Areas Group
            [
                'short_code' => 'districts',
                'name' => 'Districts',
                'perm_group_id' => $regionsAreasGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
            [
                'short_code' => 'areas',
                'name' => 'Areas',
                'perm_group_id' => $regionsAreasGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
            // Products Group
            [
                'short_code' => 'products',
                'name' => 'Products',
                'perm_group_id' => $productsGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
            [
                'short_code' => 'inventories',
                'name' => 'Inventories',
                'perm_group_id' => $productsGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
            // Orders Group
            [
                'short_code' => 'retailer_orders',
                'name' => 'Retailer Orders',
                'perm_group_id' => $ordersGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
        ];

        foreach ($categoriesToCreate as $categoryData) {
            PermissionCategory::firstOrCreate(
                ['short_code' => $categoryData['short_code']],
                $categoryData
            );
        }

        // Now retrieve all permission categories after they have been created
        $permissionCategories = PermissionCategory::all();
        $actions = ['view', 'add', 'edit', 'delete'];

        foreach ($permissionCategories as $category) {
            foreach ($actions as $action) {
                $enableFlag = 'enable_' . $action;
                if ($category->$enableFlag) {
                    Permission::updateOrCreate(
                        [
                            'name' => $action . ' ' . $category->short_code,
                            'guard_name' => 'web',
                        ],
                        [
                            'permission_category_id' => $category->id,
                        ]
                    );
                }
            }
        }

        // Assign existing user-related permissions to the "Users" category
        // These are specific permissions that don't follow the action short_code pattern directly
        $usersCategory = PermissionCategory::where('short_code', 'users')->first();
        if ($usersCategory) {
            $userPermissions = [
                'add user',
                'create distributors',
                'create managers',
                'create fieldstaff',
                'create retailers',
            ];

            foreach ($userPermissions as $permissionName) {
                Permission::updateOrCreate(
                    [
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ],
                    [
                        'permission_category_id' => $usersCategory->id,
                    ]
                );
            }
        }

        // Add specific permissions for Retailer Orders
        $retailerOrdersCategory = PermissionCategory::where('short_code', 'retailer_orders')->first();
        if ($retailerOrdersCategory) {
            $specificRetailerOrderPermissions = [
                'assign_distributor retailer_orders',
                'assign_fieldstaff retailer_orders',
                'update_delivery_status retailer_orders',
                'view my orders',
            ];

            foreach ($specificRetailerOrderPermissions as $permissionName) {
                Permission::updateOrCreate(
                    [
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ],
                    [
                        'permission_category_id' => $retailerOrdersCategory->id,
                    ]
                );
            }
        }

        // Populate spatie_roles_permissions table based on custom roles_permissions
        $roles = \App\Models\Role::all();
        $actions = ['view', 'add', 'edit', 'delete'];

        foreach ($roles as $role) {
            $customRolePermissions = \Illuminate\Support\Facades\DB::table('roles_permissions')
                ->where('role_id', $role->id)
                ->get();

            $permissionsToSync = [];

            foreach ($customRolePermissions as $customPermission) {
                $permissionCategory = PermissionCategory::find($customPermission->permission_category_id);

                if ($permissionCategory) {
                    foreach ($actions as $action) {
                        $canAction = 'can_' . $action;
                        if ($customPermission->$canAction) {
                            $permissionName = $action . ' ' . $permissionCategory->short_code;
                            $permission = Permission::where('name', $permissionName)->first();
                            if ($permission) {
                                $permissionsToSync[] = $permission->id;
                            }
                        }
                    }
                }
            }
            // Also handle specific permissions that are not tied to categories directly
            // For example, 'view my orders'
            $specificPermissions = [
                'view my orders',
                'assign_distributor retailer_orders',
                'assign_fieldstaff retailer_orders',
                'update_delivery_status retailer_orders',
                'add user',
                'create distributors',
                'create managers',
                'create fieldstaff',
                'create retailers',
            ];

            foreach ($specificPermissions as $permName) {
                $permission = Permission::where('name', $permName)->first();
                if ($permission) {
                    // Check if the role has this specific permission granted in the custom roles_permissions table
                    // This part needs careful consideration as specific permissions might not be directly in roles_permissions
                    // For now, we'll assume if it's in specificPermissions, it should be synced if the role has any related category permission.
                    // A more robust solution would involve a separate table for specific permissions or a more complex check.
                    // For simplicity, let's just add them if they exist in the permissions table.
                    $permissionsToSync[] = $permission->id;
                }
            }

            $role->syncPermissions($permissionsToSync);
        }
    }
}
