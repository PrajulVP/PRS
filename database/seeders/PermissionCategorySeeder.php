<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermissionGroup;
use App\Models\PermissionCategory;

class PermissionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permission groups
        $userManagementGroup = PermissionGroup::firstOrCreate(['name' => 'User Management']);
        $regionsAreaGroup = PermissionGroup::firstOrCreate(['name' => 'Regions & Area']);
        $productsGroup = PermissionGroup::firstOrCreate(['name' => 'Products']);
        $ordersGroup = PermissionGroup::firstOrCreate(['name' => 'Orders']);

        $categories = [
            // User Management
            // Permissions category removed as per request
            [
                'name' => 'Distributors',
                'short_code' => 'distributors',
                'perm_group_id' => $userManagementGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
            [
                'name' => 'Sales Managers',
                'short_code' => 'sales_managers',
                'perm_group_id' => $userManagementGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true, // Assuming edit is also needed
                'enable_delete' => true, // Assuming delete is also needed
            ],
            [
                'name' => 'Field Staff',
                'short_code' => 'field_staff',
                'perm_group_id' => $userManagementGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
            [
                'name' => 'Retailers',
                'short_code' => 'retailers',
                'perm_group_id' => $userManagementGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],

            // Regions & Area
            [
                'name' => 'Districts',
                'short_code' => 'districts',
                'perm_group_id' => $regionsAreaGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
            [
                'name' => 'Areas',
                'short_code' => 'areas',
                'perm_group_id' => $regionsAreaGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],

            // Products
            [
                'name' => 'Products',
                'short_code' => 'products',
                'perm_group_id' => $productsGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],

            // Orders
            [
                'name' => 'Retailer Orders',
                'short_code' => 'retailer_orders',
                'perm_group_id' => $ordersGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
            [
                'name' => 'Distributor Orders',
                'short_code' => 'distributor_orders',
                'perm_group_id' => $ordersGroup->id,
                'enable_view' => true,
                'enable_add' => true,
                'enable_edit' => true,
                'enable_delete' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            PermissionCategory::firstOrCreate(
                ['short_code' => $categoryData['short_code']],
                $categoryData
            );
        }
    }
}
