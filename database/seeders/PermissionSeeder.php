<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission; // Use our extended Permission model
use App\Models\PermissionCategory;
use App\Models\PermissionGroup; // Import PermissionGroup model

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find or create the "Orders" permission group
        $ordersGroup = PermissionGroup::firstOrCreate(['name' => 'Orders']);

        $permissionCategories = PermissionCategory::all();
        $actions = ['view', 'add', 'edit', 'delete'];

        foreach ($permissionCategories as $category) {
            foreach ($actions as $action) {
                $enableFlag = 'enable_' . $action;
                if ($category->$enableFlag) {
                    Permission::updateOrCreate(
                        [
                            'name' => $action . ' ' . $category->short_code,
                            'guard_name' => 'web',
                        ],
                        [
                            'permission_category_id' => $category->id,
                        ]
                    );
                }
            }
        }

        // Add new categories and their permissions
        $newCategories = [
            [
                'name' => 'Retailer Orders',
                'short_code' => 'retailer_orders',
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
                'perm_group_id' => $ordersGroup->id, // Assign to Orders group
            ],
            [
                'name' => 'Distributor Bulk Orders',
                'short_code' => 'distributor_bulk_orders',
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
                'perm_group_id' => $ordersGroup->id, // Assign to Orders group
            ],
        ];

        foreach ($newCategories as $newCategoryData) {
            $category = PermissionCategory::firstOrCreate(
                ['short_code' => $newCategoryData['short_code']],
                $newCategoryData
            );

            foreach ($actions as $action) {
                $enableFlag = 'enable_' . $action;
                if ($category->$enableFlag) {
                    Permission::updateOrCreate(
                        [
                            'name' => $action . ' ' . $category->short_code,
                            'guard_name' => 'web',
                        ],
                        [
                            'permission_category_id' => $category->id,
                        ]
                    );
                }
            }
        }

        // Add specific permissions for Retailer Orders
        $retailerOrdersCategory = PermissionCategory::where('short_code', 'retailer_orders')->first();
        if ($retailerOrdersCategory) {
            $specificRetailerOrderPermissions = [
                'assign_distributor retailer_orders',
                'assign_fieldstaff retailer_orders',
                'update_delivery_status retailer_orders',
                'view my orders', // Added this permission
            ];

            foreach ($specificRetailerOrderPermissions as $permissionName) {
                Permission::updateOrCreate(
                    [
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ],
                    [
                        'permission_category_id' => $retailerOrdersCategory->id,
                    ]
                );
            }
        }
    }
}
