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
        $group = PermissionGroup::firstOrCreate(['name' => 'Loyalty Management']);

        // 2. Create or Find Category
        $category = PermissionCategory::firstOrCreate(
            ['short_code' => 'loyalty_points'],
            [
                'name' => 'Loyalty Points',
                'perm_group_id' => $group->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ]
        );

        // 3. Assign Permissions
        $rolesToView = ['superadmin', 'admin', 'salesmanager', 'fieldstaff', 'retailer', 'distributor'];
        $rolesToManage = ['superadmin', 'admin'];

        foreach ($rolesToView as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                DB::table('roles_permissions')->updateOrInsert(
                    ['role_id' => $role->id, 'permission_category_id' => $category->id],
                    [
                        'can_view' => true,
                        'can_add' => in_array($roleName, $rolesToManage),
                        'can_edit' => in_array($roleName, $rolesToManage),
                        'can_delete' => in_array($roleName, $rolesToManage),
                        'updated_at' => now(),
                    ]
                );

                // Also ensure Spatie permissions exist for this category
                foreach (['view', 'add', 'edit', 'delete'] as $action) {
                    \Spatie\Permission\Models\Permission::firstOrCreate([
                        'name' => "$action loyalty_points",
                        'guard_name' => 'web'
                    ]);
                }
            }
        }
    }
}
