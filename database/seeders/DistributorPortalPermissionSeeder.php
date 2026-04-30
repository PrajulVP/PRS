<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermissionGroup;
use App\Models\PermissionCategory;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class DistributorPortalPermissionSeeder extends Seeder
{
    public function run()
    {
        // 1. Create or Find Group
        $group = PermissionGroup::firstOrCreate(['name' => 'Business Intelligence']);

        // 2. Create or Find Category for Executive Reports
        $category = PermissionCategory::firstOrCreate(
            ['short_code' => 'executive_reports'],
            [
                'name' => 'Executive Reports',
                'perm_group_id' => $group->id,
                'enable_view' => true,
                'enable_add' => false,
                'enable_edit' => false,
                'enable_delete' => false,
            ]
        );

        // 3. Create or Find Category for Staff Ratings
        $ratingCategory = PermissionCategory::firstOrCreate(
            ['short_code' => 'staff_ratings'],
            [
                'name' => 'Staff Ratings',
                'perm_group_id' => $group->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => false,
            ]
        );

        // 4. Assign to Distributor role by default
        $distributorRole = Role::where('name', 'distributor')->first();
        if ($distributorRole) {
            // Assign Executive Reports
            DB::table('roles_permissions')->updateOrInsert(
                ['role_id' => $distributorRole->id, 'permission_category_id' => $category->id],
                [
                    'can_view' => true,
                    'can_add' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                    'updated_at' => now(),
                ]
            );

            // Assign Staff Ratings
            DB::table('roles_permissions')->updateOrInsert(
                ['role_id' => $distributorRole->id, 'permission_category_id' => $ratingCategory->id],
                [
                    'can_view' => true,
                    'can_add' => true,
                    'can_edit' => true,
                    'can_delete' => false,
                    'updated_at' => now(),
                ]
            );
        }

        // 5. Ensure Spatie permissions exist
        foreach (['executive_reports', 'staff_ratings'] as $code) {
            foreach (['view', 'add', 'edit', 'delete'] as $action) {
                \Spatie\Permission\Models\Permission::firstOrCreate([
                    'name' => "$action $code",
                    'guard_name' => 'web'
                ]);
            }
        }
        
        // 6. Give Superadmin full access
        $superadmin = Role::where('name', 'superadmin')->first();
        if ($superadmin) {
            foreach ([$category, $ratingCategory] as $cat) {
                DB::table('roles_permissions')->updateOrInsert(
                    ['role_id' => $superadmin->id, 'permission_category_id' => $cat->id],
                    [
                        'can_view' => true,
                        'can_add' => true,
                        'can_edit' => true,
                        'can_delete' => true,
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
