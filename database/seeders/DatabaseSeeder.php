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
            RolePermissionSeeder::class, // Syncs roles_permissions table
            PermissionSeeder::class,     // Creates actual Spatie Permissions and Groups
            LoyaltyPermissionSeeder::class, // Missing seeder for loyalty points

            SuperAdminSeeder::class, // Creates superadmin and admin users
            SalesManagerSeeder::class,

            IndianPinCodeSeeder::class, // Creates districts and areas from JSON

            DistributorSeeder::class,
            FieldStaffSeeder::class,
            RetailerSeeder::class,
            CreateRetailerEntriesSeeder::class, // Ensure all retailer users have entries
            ProductInventorySeeder::class,

            // ProductSeeder::class,    // Creates general products
            // RetailerOrderSeeder::class, // Creates dummy retailer orders

            // Application-wide settings
            \Database\Seeders\SettingSeeder::class,
        ]);
    }
}
