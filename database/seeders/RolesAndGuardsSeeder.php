<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndGuardsSeeder extends Seeder
{
    public function run()
    {
        // List all guards and their roles
        $guardsWithRoles = [
            'superadmin' => ['superadmin', 'admin', 'manager', 'distributor', 'fieldstaff', 'retailer'],
            'admin'      => ['admin', 'manager', 'distributor', 'fieldstaff', 'retailer'],
            'manager'    => ['manager', 'distributor', 'fieldstaff', 'retailer'],
            'web'        => ['user'], // optional for normal users
        ];

        foreach ($guardsWithRoles as $guard => $roles) {
            foreach ($roles as $roleName) {
                Role::firstOrCreate(
                    ['name' => $roleName, 'guard_name' => $guard]
                );
            }
        }

        $this->command->info('✅ All roles and guards have been seeded successfully.');
    }
}
