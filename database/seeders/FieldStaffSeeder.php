<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FieldStaff; // Import the FieldStaff model
use App\Models\User; // Import the User model
use Spatie\Permission\Models\Role; // Import the Role model

class FieldStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the 'fieldstaff' role exists
        $fieldStaffRole = Role::firstOrCreate(['name' => 'fieldstaff']);

        // Create 10 field staff records
        FieldStaff::factory()->count(10)->create()->each(function ($fieldStaff) use ($fieldStaffRole) {
            // Assign the fieldstaff role to the associated user
            $fieldStaff->user->assignRole($fieldStaffRole);
        });
    }
}
