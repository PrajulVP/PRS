<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PermissionCategory;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Find the 'Distributor Bulk Orders' permission category
        $bulkOrdersCategory = PermissionCategory::where('short_code', 'distributor_bulk_orders')->first();

        if ($bulkOrdersCategory) {
            // 2. Delete all Spatie permissions associated with this category
            Permission::where('permission_category_id', $bulkOrdersCategory->id)->delete();

            // 3. Delete the permission from roles_permissions table (optional if cascade delete is not set, safer to do explicit cleanup)
            \Illuminate\Support\Facades\DB::table('roles_permissions')->where('permission_category_id', $bulkOrdersCategory->id)->delete();

            // 4. Delete the category itself
            $bulkOrdersCategory->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-adding this is specific to the seeder logic and generally we don't want to restore deprecated permissions automatically without context.
    }
};
