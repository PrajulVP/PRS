<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; // Added this line

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345'),
                'role' => 'superadmin' // Keep this for the 'role' column if still used elsewhere
            ]
        );

        // Create the 'superadmin' role if it doesn't exist for the 'superadmin' guard
        $role = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'superadmin']);

        // Assign the 'superadmin' role to the user
        $superAdmin->assignRole($role);
    }
}
