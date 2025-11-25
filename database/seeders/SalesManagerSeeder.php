<?php

namespace Database\Seeders;

use App\Models\SalesManager;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SalesManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'salesmanager', 'guard_name' => 'web']);

        // Removed default sales manager user creation
    }
}
