<?php

namespace Database\Seeders;

use App\Models\SalesManager;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SalesManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'salesmanager', 'guard_name' => 'web']);

        // Create a default Sales Manager User
        $user = User::firstOrCreate(
            ['email' => 'salesmanager@example.com'],
            [
                'name' => 'Default Sales Manager',
                'password' => Hash::make('password'),
                'role' => 'salesmanager',
                'status' => 'active',
            ]
        );

        if (!$user->hasRole('salesmanager')) {
            $user->assignRole($role);
        }

        // Create the Sales Manager profile
        SalesManager::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
                'contact_no' => '9876543210',
                'address' => '123 Sales St, Business City',
            ]
        );
    }
}
