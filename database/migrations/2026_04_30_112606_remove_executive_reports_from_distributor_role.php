<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $role = \App\Models\Role::where('name', 'distributor')->first();
        $category = \App\Models\PermissionCategory::where('short_code', 'executive_reports')->first();

        if ($role && $category) {
            \DB::table('roles_permissions')
                ->where('role_id', $role->id)
                ->where('permission_category_id', $category->id)
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $role = \App\Models\Role::where('name', 'distributor')->first();
        $category = \App\Models\PermissionCategory::where('short_code', 'executive_reports')->first();

        if ($role && $category) {
            \DB::table('roles_permissions')->updateOrInsert(
                [
                    'role_id' => $role->id,
                    'permission_category_id' => $category->id,
                ],
                [
                    'can_view' => true,
                    'can_add' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                ]
            );
        }
    }
};
