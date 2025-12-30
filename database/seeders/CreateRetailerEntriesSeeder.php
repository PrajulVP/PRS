<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Retailer;
use Illuminate\Database\Seeder;

class CreateRetailerEntriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $retailerUsers = User::where('role', 'retailer')->get();

        // Check if there are any related records to assign
        $distributor = \App\Models\Distributor::inRandomOrder()->first();
        $fieldStaff = \App\Models\FieldStaff::inRandomOrder()->first();
        $salesManager = \App\Models\SalesManager::inRandomOrder()->first();

        // If no related records exist, we cannot safely create a retailer without violating FK constraints
        // In a real seeder flow, these should exist by now.
        if (!$distributor || !$fieldStaff || !$salesManager) {
            $this->command->warn("Skipping CreateRetailerEntriesSeeder: Missing dependencies (Distributor/FieldStaff/SalesManager).");
            return;
        }

        foreach ($retailerUsers as $user) {
            // Check if a Retailer entry already exists for this user
            if (!$user->retailer) {
                Retailer::create([
                    'user_id' => $user->id,
                    'field_staff_id' => $fieldStaff->id,
                    'sales_manager_id' => $salesManager->id,
                    'distributor_id' => $distributor->id,
                    'pincode' => '600001', // Dummy pincode
                ]);
            }
        }
    }
}
