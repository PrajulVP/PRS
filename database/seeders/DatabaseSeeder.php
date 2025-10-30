<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class, // Call RoleSeeder first
            SuperAdminSeeder::class,
            AdminSeeder::class,
            DistrictSeeder::class,
            RolesAndGuardsSeeder::class,
        ]);
    }
}
