<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::truncate(); // Clear existing users
        $this->call([
            RoleSeeder::class, // Call RoleSeeder first
            PermissionCategorySeeder::class,
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            DistrictSeeder::class,
        ]);
    }
}
