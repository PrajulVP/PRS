<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Retailer; // Import the Retailer model
use App\Models\User; // Import the User model
use Spatie\Permission\Models\Role; // Import the Role model

class RetailerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the 'retailer' role exists
        $retailerRole = Role::firstOrCreate(['name' => 'retailer']);

        // Create 15 retailers
        // Retailer::factory()->count(15)->create()->each(function ($retailer) use ($retailerRole) {
        //     // Assign the retailer role to the associated user
        //     $retailer->user->assignRole($retailerRole);
        // });
    }
}
