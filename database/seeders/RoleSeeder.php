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
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'superadmin']);

        // Create Admin Role
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);

        // Create Manager Role
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'manager']);

        // Create Distributor Role
        Role::firstOrCreate(['name' => 'distributor', 'guard_name' => 'distributor']);

        // Create Field Staff Role
        Role::firstOrCreate(['name' => 'fieldstaff', 'guard_name' => 'fieldstaff']);

        // Create Retailer Role
        Role::firstOrCreate(['name' => 'retailer', 'guard_name' => 'retailer']);
    }
}
