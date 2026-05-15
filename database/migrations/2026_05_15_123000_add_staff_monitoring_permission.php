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
        // Add Staff Monitoring permission category under User Management (ID: 1)
        $catId = DB::table('permission_categories')->insertGetId([
            'name' => 'Staff Monitoring',
            'short_code' => 'staff_monitoring',
            'perm_group_id' => 1,
            'enable_view' => true,
            'enable_add' => true,
            'enable_edit' => true,
            'enable_delete' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign full permissions to Super Admin, Admin, and Sales Manager by default
        $roles = DB::table('roles')->whereIn('name', ['superadmin', 'admin', 'salesmanager'])->get();
        foreach ($roles as $role) {
            DB::table('roles_permissions')->insert([
                'role_id' => $role->id,
                'permission_category_id' => $catId,
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
        $cat = DB::table('permission_categories')->where('short_code', 'staff_monitoring')->first();
        if ($cat) {
            DB::table('roles_permissions')->where('permission_category_id', $cat->id)->delete();
            DB::table('permission_categories')->where('id', $cat->id)->delete();
        }
    }
};
