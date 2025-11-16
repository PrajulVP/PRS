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
                if (in_array($category->short_code, ['managers', 'distributors', 'field_staff', 'retailers', 'products', 'retailer_orders', 'distributor_orders'])) {
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
    }
}
