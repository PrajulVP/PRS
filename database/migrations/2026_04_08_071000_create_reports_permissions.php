<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\PermissionCategory;
use App\Models\PermissionGroup;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Permission Group
        $groupId = DB::table('permission_groups')->insertGetId([
            'name' => 'Reports',
            'short_code' => 'reports',
            'is_active' => true,
            'system' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create Permission Categories
        $categories = [
            [
                'name' => 'Distributor Reports',
                'short_code' => 'distributor_reports',
                'perm_group_id' => $groupId,
                'enable_view' => true,
                'enable_add' => false,
                'enable_edit' => false,
                'enable_delete' => false,
            ],
            [
                'name' => 'Retailer Reports',
                'short_code' => 'retailer_reports',
                'perm_group_id' => $groupId,
                'enable_view' => true,
                'enable_add' => false,
                'enable_edit' => false,
                'enable_delete' => false,
            ],
            [
                'name' => 'Field Staff Performance',
                'short_code' => 'performance_reports',
                'perm_group_id' => $groupId,
                'enable_view' => true,
                'enable_add' => false,
                'enable_edit' => false,
                'enable_delete' => false,
            ],
        ];

        foreach ($categories as $cat) {
            $catId = DB::table('permission_categories')->insertGetId(array_merge($cat, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            // 3. Assign Permissions to Roles
            // Super Admin, Admin, Sales Manager get all
            $rolesToAssignAll = Role::whereIn('name', ['superadmin', 'admin', 'salesmanager'])->get();
            foreach ($rolesToAssignAll as $role) {
                DB::table('roles_permissions')->insert([
                    'role_id' => $role->id,
                    'permission_category_id' => $catId,
                    'can_view' => true,
                    'can_add' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Field Staff gets only Retailer Reports
            if ($cat['short_code'] === 'retailer_reports') {
                $fieldStaffRole = Role::where('name', 'fieldstaff')->first();
                if ($fieldStaffRole) {
                    DB::table('roles_permissions')->insert([
                        'role_id' => $fieldStaffRole->id,
                        'permission_category_id' => $catId,
                        'can_view' => true,
                        'can_add' => false,
                        'can_edit' => false,
                        'can_delete' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $group = DB::table('permission_groups')->where('short_code', 'reports')->first();
        if ($group) {
            $categoryIds = DB::table('permission_categories')->where('perm_group_id', $group->id)->pluck('id');
            DB::table('roles_permissions')->whereIn('permission_category_id', $categoryIds)->delete();
            DB::table('permission_categories')->where('perm_group_id', $group->id)->delete();
            DB::table('permission_groups')->where('id', $group->id)->delete();
        }
    }
};
