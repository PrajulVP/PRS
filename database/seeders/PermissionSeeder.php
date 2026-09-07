<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\PermissionCategory;
use App\Models\PermissionGroup;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create / Update Standard Groups
        $groups = [
            'Reports' => $this->getOrCreateGroup('Reports'),
            'User Management' => $this->getOrCreateGroup('User Management'),
            'Regions & Areas' => $this->getOrCreateGroup('Regions & Areas', ['Regions & Area']),
            'Products & Inventory' => $this->getOrCreateGroup('Products & Inventory', ['Products']),
            'Orders & Returns' => $this->getOrCreateGroup('Orders & Returns', ['Orders']),
            'Approvals' => $this->getOrCreateGroup('Approvals'),
            'Staff Monitoring' => $this->getOrCreateGroup('Staff Monitoring'),
            'Loyalty & Credits' => $this->getOrCreateGroup('Loyalty & Credits'),
        ];

        // 2. Define Category Structure matching the prompt & sidebar exact requirements
        $categoriesToCreate = [
            // --- Reports Group ---
            [
                'short_code' => 'distributor_reports',
                'name' => 'Distributor Reports',
                'perm_group_id' => $groups['Reports']->id,
                'enable_view' => true, 'enable_add' => false, 'enable_edit' => false, 'enable_delete' => false,
            ],
            [
                'short_code' => 'retailer_reports',
                'name' => 'Retailer Reports',
                'perm_group_id' => $groups['Reports']->id,
                'enable_view' => true, 'enable_add' => false, 'enable_edit' => false, 'enable_delete' => false,
            ],
            [
                'short_code' => 'performance_reports',
                'name' => 'Field Staff Performance',
                'perm_group_id' => $groups['Reports']->id,
                'enable_view' => true, 'enable_add' => false, 'enable_edit' => false, 'enable_delete' => false,
            ],
            [
                'short_code' => 'product_reports',
                'name' => 'Product Performance',
                'perm_group_id' => $groups['Reports']->id,
                'enable_view' => true, 'enable_add' => false, 'enable_edit' => false, 'enable_delete' => false,
            ],
            [
                'short_code' => 'master_order_reports',
                'name' => 'Master Order Analytics',
                'perm_group_id' => $groups['Reports']->id,
                'enable_view' => true, 'enable_add' => false, 'enable_edit' => false, 'enable_delete' => false,
            ],
            [
                'short_code' => 'executive_reports',
                'name' => 'Executive Reports',
                'perm_group_id' => $groups['Reports']->id,
                'enable_view' => true, 'enable_add' => false, 'enable_edit' => false, 'enable_delete' => false,
            ],

            // --- User Management Group ---
            [
                'short_code' => 'distributors',
                'name' => 'Distributors',
                'perm_group_id' => $groups['User Management']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => true,
            ],
            [
                'short_code' => 'sales_managers',
                'name' => 'Sales Managers',
                'perm_group_id' => $groups['User Management']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => true,
            ],
            [
                'short_code' => 'field_staff',
                'name' => 'Field Staff',
                'perm_group_id' => $groups['User Management']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => true,
            ],
            [
                'short_code' => 'retailers',
                'name' => 'Retailers',
                'perm_group_id' => $groups['User Management']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => true,
            ],

            // --- Regions & Areas Group ---
            [
                'short_code' => 'districts',
                'name' => 'Districts',
                'perm_group_id' => $groups['Regions & Areas']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => true,
            ],
            [
                'short_code' => 'areas',
                'name' => 'Areas',
                'perm_group_id' => $groups['Regions & Areas']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => true,
            ],

            // --- Products & Inventory Group ---
            [
                'short_code' => 'products',
                'name' => 'Products',
                'perm_group_id' => $groups['Products & Inventory']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => true,
            ],
            [
                'short_code' => 'inventories',
                'name' => 'Inventories',
                'perm_group_id' => $groups['Products & Inventory']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => true,
            ],

            // --- Orders & Returns Group ---
            [
                'short_code' => 'retailer_orders',
                'name' => 'Retailer Orders',
                'perm_group_id' => $groups['Orders & Returns']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => true,
            ],
            [
                'short_code' => 'distributor_orders',
                'name' => 'Distributor Orders',
                'perm_group_id' => $groups['Orders & Returns']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => true,
            ],
            [
                'short_code' => 'product_returns',
                'name' => 'Return Products',
                'perm_group_id' => $groups['Orders & Returns']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => false,
            ],

            // --- Approvals Group ---
            [
                'short_code' => 'retailer_approvals',
                'name' => 'Retailer Approvals',
                'perm_group_id' => $groups['Approvals']->id,
                'enable_view' => true, 'enable_add' => false, 'enable_edit' => true, 'enable_delete' => false,
            ],
            [
                'short_code' => 'distributor_approvals',
                'name' => 'Distributor Approvals',
                'perm_group_id' => $groups['Approvals']->id,
                'enable_view' => true, 'enable_add' => false, 'enable_edit' => true, 'enable_delete' => false,
            ],

            // --- Staff Monitoring Group ---
            [
                'short_code' => 'staff_monitoring',
                'name' => 'Staff Monitoring & Tracking',
                'perm_group_id' => $groups['Staff Monitoring']->id,
                'enable_view' => true, 'enable_add' => false, 'enable_edit' => true, 'enable_delete' => false,
            ],
            [
                'short_code' => 'staff_targets',
                'name' => 'Staff Targets & Visits',
                'perm_group_id' => $groups['Staff Monitoring']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => false,
            ],

            // --- Loyalty & Credits Group ---
            [
                'short_code' => 'loyalty_points',
                'name' => 'Loyalty Points & Rewards',
                'perm_group_id' => $groups['Loyalty & Credits']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => false,
            ],
            [
                'short_code' => 'wallets_credits',
                'name' => 'Wallets & Credits',
                'perm_group_id' => $groups['Loyalty & Credits']->id,
                'enable_view' => true, 'enable_add' => true, 'enable_edit' => true, 'enable_delete' => false,
            ],
        ];

        // 3. Upsert Categories
        foreach ($categoriesToCreate as $categoryData) {
            PermissionCategory::updateOrCreate(
                ['short_code' => $categoryData['short_code']],
                $categoryData
            );
        }

        // 4. Create Spatie Permissions for each enabled action
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

        // 5. Ensure Superadmin & Admin Roles have full permissions enabled in custom `roles_permissions`
        $allRoles = Role::all();
        $allCategories = PermissionCategory::all();

        foreach ($allRoles as $role) {
            if (in_array($role->name, ['superadmin', 'admin'])) {
                foreach ($allCategories as $cat) {
                    DB::table('roles_permissions')->updateOrInsert(
                        [
                            'role_id' => $role->id,
                            'permission_category_id' => $cat->id,
                        ],
                        [
                            'can_view' => true,
                            'can_add' => (bool) $cat->enable_add,
                            'can_edit' => (bool) $cat->enable_edit,
                            'can_delete' => (bool) $cat->enable_delete,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        }

        // 6. Sync Spatie role permissions based on custom `roles_permissions`
        foreach ($allRoles as $role) {
            $customRolePermissions = DB::table('roles_permissions')
                ->where('role_id', $role->id)
                ->get();

            $permissionsToSync = [];

            foreach ($customRolePermissions as $customPermission) {
                $permissionCategory = PermissionCategory::find($customPermission->permission_category_id);

                if ($permissionCategory) {
                    foreach ($actions as $action) {
                        $canAction = 'can_' . $action;
                        if (!empty($customPermission->$canAction)) {
                            $permissionName = $action . ' ' . $permissionCategory->short_code;
                            $permission = Permission::where('name', $permissionName)->first();
                            if ($permission) {
                                $permissionsToSync[] = $permission->id;
                            }
                        }
                    }
                }
            }

            $role->syncPermissions($permissionsToSync);
        }
    }

    /**
     * Helper to get or create a group, with optional fallback names to rename
     */
    private function getOrCreateGroup(string $name, array $fallbacks = []): PermissionGroup
    {
        $group = PermissionGroup::where('name', $name)->first();
        if (!$group && !empty($fallbacks)) {
            $group = PermissionGroup::whereIn('name', $fallbacks)->first();
            if ($group) {
                $group->update(['name' => $name]);
            }
        }

        if (!$group) {
            $group = PermissionGroup::firstOrCreate(['name' => $name]);
        }

        return $group;
    }
}
