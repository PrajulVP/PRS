<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\FieldStaff;
use App\Models\Distributor;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class FieldStaffUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fieldStaffUser = User::updateOrCreate(
            ['email' => 'fieldstaff@gmail.com'],
            [
                'name' => 'Field Staff User',
                'password' => Hash::make('12345'),
                'role' => 'fieldstaff'
            ]
        );

        $role = Role::firstOrCreate(['name' => 'fieldstaff', 'guard_name' => 'web']);
        $fieldStaffUser->assignRole($role);

        // Assuming a distributor exists from DistributorUserSeeder
        $distributor = Distributor::first(); 
        if (!$distributor) {
            // Fallback if no distributor exists, though DistributorUserSeeder should run first
            $distributor = Distributor::factory()->create(['user_id' => User::factory()->create()->id]);
        }

        // Create associated FieldStaff model
        FieldStaff::updateOrCreate(
            ['user_id' => $fieldStaffUser->id],
            [
                'distributor_id' => $distributor->id,
                'status' => 'active',
            ]
        );
    }
}
