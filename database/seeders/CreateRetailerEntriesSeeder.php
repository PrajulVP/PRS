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

        foreach ($retailerUsers as $user) {
            // Check if a Retailer entry already exists for this user
            if (!$user->retailer) {
                Retailer::create([
                    'user_id' => $user->id,
                    // Add any other default fields for Retailer if necessary
                ]);
            }
        }
    }
}