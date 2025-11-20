<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Manager; // Import the Manager model
use App\Models\Distributor; // Import the Distributor model
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managerUser = User::updateOrCreate(
            ['email' => 'manager@gmail.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('12345'),
                'role' => 'manager',
                'status' => 'active', // Set to active for seeding purposes
            ]
        );

        $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerUser->assignRole($role);

        // Find an existing distributor or create one (should exist from DistributorUserSeeder)
        $distributor = Distributor::first();
        if (!$distributor) {
            // Fallback if no distributor exists
            $distributor = Distributor::factory()->create(['user_id' => User::factory()->create()->id]);
        }

        // Create associated Manager model
        Manager::updateOrCreate(
            ['user_id' => $managerUser->id],
            [
                'distributor_id' => $distributor->id,
                'name' => $managerUser->name,
                'email' => $managerUser->email,
                'contact_no' => '9876543210', // Sample data
                'address' => '123 Manager St, Sample City', // Sample data
                'status' => 'active', // Set manager's profile status to active for seeding
            ]
        );
    }
}
