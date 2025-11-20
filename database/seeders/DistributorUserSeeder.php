<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Distributor;
use App\Models\District;
use App\Models\Area;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DistributorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $distributorUser = User::updateOrCreate(
            ['email' => 'distributor@gmail.com'],
            [
                'name' => 'Distributor User',
                'password' => Hash::make('12345'),
                'role' => 'distributor'
            ]
        );

        $role = Role::firstOrCreate(['name' => 'distributor', 'guard_name' => 'web']);
        $distributorUser->assignRole($role);

        // Create a District and Area for the distributor
        $district = District::firstOrCreate(['name' => 'Test District D']);
        $area = Area::firstOrCreate(['name' => 'Test Area D', 'district_id' => $district->id]);

        // Create associated Distributor model
        Distributor::updateOrCreate(
            ['user_id' => $distributorUser->id],
            [
                'gst' => '22AAAAA0000A1Z5',
                'drug_license_number' => 'DL-12345',
                'contact_no' => '9876543210',
                'address' => '123 Distributor St, Test City D',
                'pincode' => '123456',
                'district_id' => $district->id,
                'area_id' => $area->id,
                'route' => 'Route D',
            ]
        );
    }
}
