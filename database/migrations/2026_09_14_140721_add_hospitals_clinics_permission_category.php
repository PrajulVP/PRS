<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $group = DB::table('permission_groups')->where('name', 'User Management')->first();
        if (!$group) {
            $group = DB::table('permission_groups')->first();
        }

        $categoryId = DB::table('permission_categories')->insertGetId([
            'perm_group_id' => $group ? $group->id : 1,
            'name' => 'Hospitals / Clinics',
            'short_code' => 'hospitals_clinics',
            'enable_view' => true,
            'enable_add' => true,
            'enable_edit' => true,
            'enable_delete' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Auto-assign permission to superadmin and admin roles
        $roles = DB::table('roles')->whereIn('name', ['superadmin', 'admin'])->get();
        foreach ($roles as $role) {
            DB::table('roles_permissions')->insert([
                'role_id' => $role->id,
                'permission_category_id' => $categoryId,
                'can_view' => true,
                'can_add' => true,
                'can_edit' => true,
                'can_delete' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $category = DB::table('permission_categories')->where('short_code', 'hospitals_clinics')->first();
        if ($category) {
            DB::table('roles_permissions')->where('permission_category_id', $category->id)->delete();
            DB::table('permission_categories')->where('id', $category->id)->delete();
        }
    }
};
