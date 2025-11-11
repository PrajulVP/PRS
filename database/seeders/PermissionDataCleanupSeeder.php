<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\PermissionCategory;
use Illuminate\Support\Facades\DB;

class PermissionDataCleanupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing permission category IDs
        $existingCategoryIds = PermissionCategory::pluck('id')->toArray();

        // Delete permissions that reference non-existent categories
        Permission::whereNotIn('permission_category_id', $existingCategoryIds)->delete();

        // Get all existing permission IDs
        $existingPermissionIds = Permission::pluck('id')->toArray();

        // Delete roles_permissions entries that reference non-existent permissions
        DB::table('roles_permissions')->whereNotIn('permission_id', $existingPermissionIds)->delete();
    }
}
