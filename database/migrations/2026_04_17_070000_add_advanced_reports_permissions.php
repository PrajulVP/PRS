<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Find the Reports Group
        $group = DB::table('permission_groups')->where('short_code', 'reports')->first();
        
        if (!$group) {
            // Fallback: search for Name if short_code didn't exist in early versions
            $group = DB::table('permission_groups')->where('name', 'Reports')->first();
        }

        if ($group) {
            $groupId = $group->id;

            // 2. Define New Categories
            $categories = [
                [
                    'name' => 'Product Performance',
                    'short_code' => 'product_reports',
                    'perm_group_id' => $groupId,
                    'enable_view' => true,
                    'enable_add' => false,
                    'enable_edit' => false,
                    'enable_delete' => false,
                ],
                [
                    'name' => 'Master Order Analytics',
                    'short_code' => 'master_order_reports',
                    'perm_group_id' => $groupId,
                    'enable_view' => true,
                    'enable_add' => false,
                    'enable_edit' => false,
                    'enable_delete' => false,
                ],
            ];

            foreach ($categories as $cat) {
                // Check if already exists to prevent duplicate on re-run
                $exists = DB::table('permission_categories')
                    ->where('short_code', $cat['short_code'])
                    ->exists();

                if (!$exists) {
                    $catId = DB::table('permission_categories')->insertGetId(array_merge($cat, [
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));

                    // 3. Assign to Admins by default
                    $adminRoles = Role::whereIn('name', ['superadmin', 'admin', 'salesmanager'])->get();
                    foreach ($adminRoles as $role) {
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
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $shortCodes = ['product_reports', 'master_order_reports'];
        $categoryIds = DB::table('permission_categories')
            ->whereIn('short_code', $shortCodes)
            ->pluck('id');

        DB::table('roles_permissions')->whereIn('permission_category_id', $categoryIds)->delete();
        DB::table('permission_categories')->whereIn('id', $categoryIds)->delete();
    }
};
