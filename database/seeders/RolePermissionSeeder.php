<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\PermissionCategory;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadminRole = Role::where('name', 'superadmin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $permissionCategories = PermissionCategory::all();

        // Seed permissions for Superadmin (role_id = 1)
        if ($superadminRole) {
            foreach ($permissionCategories as $category) {
                DB::table('roles_permissions')->updateOrInsert(
                    [
                        'role_id' => $superadminRole->id,
                        'permission_category_id' => $category->id,
                    ],
                    [
                        'can_view' => true,
                        'can_add' => true,
                        'can_edit' => true,
                        'can_delete' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Seed permissions for Admin (role_id = 2)
        // Admin permissions can be editable by superadmin, so we'll give them some default access.
        if ($adminRole) {
            foreach ($permissionCategories as $category) {
                // Example: Admin can view all, but only add/edit/delete for some categories
                $canView = true;
                $canAdd = false;
                $canEdit = false;
                $canDelete = false;

                // Customize admin permissions here based on typical admin needs
                if (in_array($category->short_code, [
                    'managers', 
                    'distributors', 
                    'field_staff', 
                    'retailers', 
                    'products', 
                    'inventories',
                    'loyalty_points',
                    'retailer_orders', 
                    'distributor_orders', 
                    'retailer_approvals', 
                    'distributor_approvals'
                ])) {
                    $canAdd = true;
                    $canEdit = true;
                    $canDelete = true;
                }

                DB::table('roles_permissions')->updateOrInsert(
                    [
                        'role_id' => $adminRole->id,
                        'permission_category_id' => $category->id,
                    ],
                    [
                        'can_view' => $canView,
                        'can_add' => $canAdd,
                        'can_edit' => $canEdit,
                        'can_delete' => $canDelete,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Seed permissions for Retailer
        $retailerRole = Role::where('name', 'retailer')->first();
        if ($retailerRole) {
            $retailerOrdersCategory = PermissionCategory::where('short_code', 'retailer_orders')->first();
            if ($retailerOrdersCategory) {
                DB::table('roles_permissions')->updateOrInsert(
                    [
                        'role_id' => $retailerRole->id,
                        'permission_category_id' => $retailerOrdersCategory->id,
                    ],
                    [
                        'can_view' => true,
                        'can_add' => false,
                        'can_edit' => false,
                        'can_delete' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Seed permissions for Field Staff
        $fieldstaffRole = Role::where('name', 'fieldstaff')->first();
        if ($fieldstaffRole) {
            $distributorOrdersCategory = PermissionCategory::where('short_code', 'distributor_orders')->first();
            if ($distributorOrdersCategory) {
                DB::table('roles_permissions')->updateOrInsert(
                    [
                        'role_id' => $fieldstaffRole->id,
                        'permission_category_id' => $distributorOrdersCategory->id,
                    ],
                    [
                        'can_view' => true,
                        'can_add' => false,
                        'can_edit' => false,
                        'can_delete' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Add retailer_approvals permission
            $retailerApprovalsCategory = PermissionCategory::where('short_code', 'retailer_approvals')->first();
            if ($retailerApprovalsCategory) {
                DB::table('roles_permissions')->updateOrInsert(
                    [
                        'role_id' => $fieldstaffRole->id,
                        'permission_category_id' => $retailerApprovalsCategory->id,
                    ],
                    [
                        'can_view' => true,
                        'can_add' => false,
                        'can_edit' => true,
                        'can_delete' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Seed permissions for Distributor
        $distributorRole = Role::where('name', 'distributor')->first();
        if ($distributorRole) {
            $distributorOrdersCategory = PermissionCategory::where('short_code', 'distributor_orders')->first();
            if ($distributorOrdersCategory) {
                DB::table('roles_permissions')->updateOrInsert(
                    [
                        'role_id' => $distributorRole->id,
                        'permission_category_id' => $distributorOrdersCategory->id,
                    ],
                    [
                        'can_view' => true,
                        'can_add' => true, // Distributors can create their own orders
                        'can_edit' => true,
                        'can_delete' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            // Also give distributors view permission for retailer orders
            $retailerOrdersCategory = PermissionCategory::where('short_code', 'retailer_orders')->first();
            if ($retailerOrdersCategory) {
                DB::table('roles_permissions')->updateOrInsert(
                    [
                        'role_id' => $distributorRole->id,
                        'permission_category_id' => $retailerOrdersCategory->id,
                    ],
                    [
                        'can_view' => true,
                        'can_add' => false,
                        'can_edit' => false,
                        'can_delete' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Add retailer_approvals permission
            $retailerApprovalsCategory = PermissionCategory::where('short_code', 'retailer_approvals')->first();
            if ($retailerApprovalsCategory) {
                DB::table('roles_permissions')->updateOrInsert(
                    [
                        'role_id' => $distributorRole->id,
                        'permission_category_id' => $retailerApprovalsCategory->id,
                    ],
                    [
                        'can_view' => true,
                        'can_add' => false,
                        'can_edit' => true,
                        'can_delete' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
