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
        // Truncate users and roles/permissions tables
        // User::truncate(); // Commented out to prevent truncating users created by explicit seeders
        // Use `php artisan migrate:fresh --seed` instead of truncate for a clean state
        
        $this->call([
            RoleSeeder::class, // Call RoleSeeder first
            PermissionCategorySeeder::class,
            RolePermissionSeeder::class,
            SuperAdminSeeder::class, // Creates superadmin and admin users
            ManagerSeeder::class,    // Creates manager user

            DistrictSeeder::class,   // Creates general districts
            AreaSeeder::class,       // Creates general areas

            DistributorUserSeeder::class, // Creates specific distributor user and associated model
            FieldStaffUserSeeder::class,  // Creates specific fieldstaff user and associated model
            RetailerUserSeeder::class,    // Creates specific retailer user and associated model
            
            ProductSeeder::class,    // Creates general products
            // If there are other factory-based seeders that create many records,
            // they can be called here (e.g., ProductFactory)
        ]);
    }
}
