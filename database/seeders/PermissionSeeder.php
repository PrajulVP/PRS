<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission; // Use our extended Permission model
use App\Models\PermissionCategory;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionCategories = PermissionCategory::all();
        $actions = ['view', 'add', 'edit', 'delete'];

        foreach ($permissionCategories as $category) {
            foreach ($actions as $action) {
                $enableFlag = 'enable_' . $action;
                if ($category->$enableFlag) {
                    $permissionName = $action . ' ' . $category->short_code;
                    Permission::firstOrCreate(
                        [
                            'name' => $permissionName,
                            'guard_name' => 'web',
                            'permission_category_id' => $category->id,
                        ]
                    );
                }
            }
        }
    }
}
