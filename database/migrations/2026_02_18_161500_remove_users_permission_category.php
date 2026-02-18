<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PermissionCategory;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Find the 'Users' permission category
        $usersCategory = PermissionCategory::where('short_code', 'users')->first();

        if ($usersCategory) {
            // 2. Delete all Spatie permissions associated with this category
            // This includes 'view users', 'add users', etc., AND specific permissions like 'add user' linked to this category ID
            Permission::where('permission_category_id', $usersCategory->id)->delete();

            // 3. Delete the permissions from roles_permissions table
            DB::table('roles_permissions')->where('permission_category_id', $usersCategory->id)->delete();

            // 4. Delete the category itself
            $usersCategory->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal logic omitted as it involves restoring deleted data.
    }
};
