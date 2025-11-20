<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manager = User::updateOrCreate(
            ['email' => 'manager@gmail.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('12345'),
                'role' => 'manager'
            ]
        );

        $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->assignRole($role);
    }
}
