<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PermissionGroup;
use App\Models\PermissionCategory;
use Spatie\Permission\Models\Role;

class LoyaltyPermissionSeeder extends Seeder
{
    public function run()
    {
        // 1. Create or Find Group
        $group = PermissionGroup::firstOrCreate(['name' => 'Loyalty']);

        // 2. Create or Find Category
        $category = PermissionCategory::firstOrCreate(
            ['short_code' => 'loyalty_points'],
            [
                'name' => 'Loyalty Points',
                'permission_group_id' => $group->id,
                'description' => 'Manage and view loyalty points'
            ]
        );

        // 3. Assign Permissions
        $rolesToView = ['superadmin', 'admin', 'salesmanager', 'fieldstaff', 'retailer', 'distributor'];
        $rolesToManage = ['superadmin', 'admin']; // Only admins can edit/add/delete points via this (though logic is automatic)

        foreach ($rolesToView as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                // Check if permission already exists
                $exists = DB::table('roles_permissions')
                    ->where('role_id', $role->id)
                    ->where('permission_category_id', $category->id)
                    ->exists();

                if (!$exists) {
                    DB::table('roles_permissions')->insert([
                        'role_id' => $role->id,
                        'permission_category_id' => $category->id,
                        'can_view' => true,
                        'can_add' => in_array($roleName, $rolesToManage),
                        'can_edit' => in_array($roleName, $rolesToManage),
                        'can_delete' => in_array($roleName, $rolesToManage),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
