<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Superadmin Role
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        // Create Admin Role
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Create Manager Role
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);

        // Create Distributor Role
        Role::firstOrCreate(['name' => 'distributor', 'guard_name' => 'web']);

        // Create Field Staff Role
        Role::firstOrCreate(['name' => 'fieldstaff', 'guard_name' => 'web']);

        // Create Retailer Role
        Role::firstOrCreate(['name' => 'retailer', 'guard_name' => 'web']);
    }
}