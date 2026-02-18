<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\PermissionCategory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Get the Inventory permission category
        $inventoryCategory = PermissionCategory::where('short_code', 'inventory')->first();

        if ($inventoryCategory) {
            // 2. Identify the Inventory permissions
            $inventoryPermissions = Permission::where('permission_category_id', $inventoryCategory->id)->get();

            // 3. Grant these permissions to Superadmin (and Admin)
            $roles = Role::whereIn('name', ['superadmin', 'admin'])->get();

            foreach ($roles as $role) {
                $role->givePermissionTo($inventoryPermissions);

                // 4. Also syncing manually to the custom roles_permissions table if used for UI
                foreach ($inventoryPermissions as $permission) {
                    $canAction = explode(' ', $permission->name)[0]; // view, add, edit, delete

                    \Illuminate\Support\Facades\DB::table('roles_permissions')->updateOrInsert(
                        [
                            'role_id' => $role->id,
                            'permission_category_id' => $inventoryCategory->id,
                        ],
                        [
                            'can_' . $canAction => true,
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed for permission assignment
    }
};
