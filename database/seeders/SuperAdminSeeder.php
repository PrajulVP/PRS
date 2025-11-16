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
        $role = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        // Assign the 'superadmin' role to the user
        $superAdmin->assignRole($role);

        // Create or update the 'admin' user
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345'),
                'role' => 'admin'
            ]
        );

        // Create the 'admin' role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Assign the 'admin' role to the admin user
        $adminUser->assignRole($adminRole);
    }
}
