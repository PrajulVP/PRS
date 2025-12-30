<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Retailer; // Import the Retailer model
use App\Models\User; // Import the User model
use Spatie\Permission\Models\Role; // Import the Role model

class RetailerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the 'retailer' role exists
        $retailerRole = Role::firstOrCreate(['name' => 'retailer']);

        // Get existing related models to avoid creating new ones if possible, or fallback to factories
        $distributorIds = \App\Models\Distributor::pluck('id');
        $fieldStaffIds = \App\Models\FieldStaff::pluck('id');
        $salesManagerIds = \App\Models\SalesManager::pluck('id');

        // Create 15 retailers
        Retailer::factory()->count(15)->make()->each(function ($retailerData) use ($retailerRole, $distributorIds, $fieldStaffIds, $salesManagerIds) {

            // Create user first
            $user = User::factory()->create([
                'name' => $retailerData->proprietor_name, // Sync name
                'role' => 'retailer'
            ]);
            $user->assignRole($retailerRole);

            // Assign relationships randomly from existing or new
            $distributorId = $distributorIds->isNotEmpty() ? $distributorIds->random() : \App\Models\Distributor::factory()->create()->id;
            $fieldStaffId = $fieldStaffIds->isNotEmpty() ? $fieldStaffIds->random() : \App\Models\FieldStaff::factory()->create()->id;
            $salesManagerId = $salesManagerIds->isNotEmpty() ? $salesManagerIds->random() : \App\Models\SalesManager::factory()->create()->id;

            // Create retailer
            $retailerDataArray = $retailerData->toArray();
            unset($retailerDataArray['proprietor_name']);

            Retailer::create(array_merge($retailerDataArray, [
                'user_id' => $user->id,
                'distributor_id' => $distributorId,
                'field_staff_id' => $fieldStaffId,
                'sales_manager_id' => $salesManagerId,
            ]));
        });
    }
}
