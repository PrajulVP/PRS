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
        // Standardize groups (Search by old/new names to prevent duplicates)
        $userManagementGroup = $this->getOrCreateGroup('User Management');
        $regionsAreaGroup = $this->getOrCreateGroup('Regions & Areas', ['Regions & Area']);
        $productsGroup = $this->getOrCreateGroup('Products');
        $ordersGroup = $this->getOrCreateGroup('Orders');
        $approvalsGroup = $this->getOrCreateGroup('Approvals');

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

            // Regions & Areas Group
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
            // Approvals
            [
                'name' => 'Retailer Approvals',
                'short_code' => 'retailer_approvals',
                'perm_group_id' => $approvalsGroup->id,
                'enable_view' => true,
                'enable_add' => false,
                'enable_edit' => true, // Allows 'Approve/Reject' actions
                'enable_delete' => false,
            ],
            [
                'name' => 'Distributor Approvals',
                'short_code' => 'distributor_approvals',
                'perm_group_id' => $approvalsGroup->id,
                'enable_view' => true,
                'enable_add' => false,
                'enable_edit' => true,
                'enable_delete' => false,
            ],
        ];

        foreach ($categories as $categoryData) {
            PermissionCategory::firstOrCreate(
                ['short_code' => $categoryData['short_code']],
                $categoryData
            );
        }
    }

    /**
     * Helper to get or create a group, with optional fallback names to rename
     */
    private function getOrCreateGroup(string $name, array $fallbacks = []): PermissionGroup
    {
        $group = PermissionGroup::where('name', $name)->first();
        if (!$group && !empty($fallbacks)) {
            $group = PermissionGroup::whereIn('name', $fallbacks)->first();
            if ($group) {
                $group->update(['name' => $name]);
            }
        }
        
        if (!$group) {
            $group = PermissionGroup::firstOrCreate(['name' => $name]);
        }
        
        return $group;
    }
}
