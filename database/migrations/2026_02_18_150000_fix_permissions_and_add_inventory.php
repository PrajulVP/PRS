<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PermissionGroup;
use App\Models\PermissionCategory;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Remove duplicate 'Regions & Areas' group (ID 5 or via short_code)
        $duplicateGroup = PermissionGroup::where('short_code', 'regions_areas')->where('system', 1)->first();
        if ($duplicateGroup) {
            // Ensure no categories are using this group before delete, or move them to the valid one
            // Based on investigation, categories are using Group ID 2 ("Regions & Area")
            $duplicateGroup->delete();
        }

        // 2. Add 'Inventory' Permission Category
        $productsGroup = PermissionGroup::where('name', 'Products')->first();

        if ($productsGroup) {
            $inventoryCategory = PermissionCategory::firstOrCreate(
                ['short_code' => 'inventory'],
                [
                    'name' => 'Inventory',
                    'perm_group_id' => $productsGroup->id,
                    'enable_view' => true,
                    'enable_add' => true,
                    'enable_edit' => true,
                    'enable_delete' => true,
                ]
            );

            // Create Spatie Permissions for Inventory
            $actions = ['view', 'add', 'edit', 'delete'];
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => $action . ' inventory', 'guard_name' => 'web', 'permission_category_id' => $inventoryCategory->id]);
            }

            // Assign Inventory permissions to Admin and Superadmin
            $roles = \Spatie\Permission\Models\Role::whereIn('name', ['admin', 'superadmin'])->get();
            foreach ($roles as $role) {
                $role->givePermissionTo(Permission::where('permission_category_id', $inventoryCategory->id)->get());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We generally don't reverse permission creations in this context as it might break things,
        // but we can remove the inventory permissions.
        $category = PermissionCategory::where('short_code', 'inventory')->first();
        if ($category) {
            Permission::where('permission_category_id', $category->id)->delete();
            $category->delete();
        }
    }
};
