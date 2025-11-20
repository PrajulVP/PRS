<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Retailer;
use App\Models\Distributor;
use App\Models\District;
use App\Models\Area;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RetailerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $retailerUser = User::updateOrCreate(
            ['email' => 'retailer@gmail.com'],
            [
                'name' => 'Retailer User',
                'password' => Hash::make('12345'),
                'role' => 'retailer'
            ]
        );

        $role = Role::firstOrCreate(['name' => 'retailer', 'guard_name' => 'web']);
        $retailerUser->assignRole($role);

        // Find or create a District and Area
        $district = District::firstOrCreate(['name' => 'Test District R']);
        $area = Area::firstOrCreate(['name' => 'Test Area R', 'district_id' => $district->id]);

        // Find an existing distributor or create one (should exist from DistributorUserSeeder)
        $distributor = Distributor::first();
        if (!$distributor) {
            // Fallback if no distributor exists
            $distributor = Distributor::factory()->create(['user_id' => User::factory()->create()->id]);
        }

        // Create associated Retailer model
        Retailer::updateOrCreate(
            ['user_id' => $retailerUser->id],
            [
                'distributor_id' => $distributor->id,
                'field_staff_id' => null, // Will be assigned later or by another seeder/logic
                'district_id' => $district->id,
                'area_id' => $area->id,
                'proprietor_name' => 'Retailer Proprietor',
                'contact_no' => '9988776655',
                'gst' => '22BBBBB0000B1Z6',
                'address' => '456 Retailer Ave, Test City R',
                'status' => 'active',
                'credit_limit' => 50000.00,
            ]
        );
    }
}
