<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Distributor; // Import the Distributor model
use App\Models\User; // Import the User model
use Spatie\Permission\Models\Role; // Import the Role model

class DistributorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the 'distributor' role exists
        $distributorRole = Role::firstOrCreate(['name' => 'distributor']);

        // Create 5 distributors
        // Distributor::factory()->count(5)->create()->each(function ($distributor) use ($distributorRole) {
        //     // Assign the distributor role to the associated user
        //     $distributor->user->assignRole($distributorRole);
        // });
    }
}
