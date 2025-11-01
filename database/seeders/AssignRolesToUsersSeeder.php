<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignRolesToUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($user->email === 'superadmin@gmail.com') {
                $role = Role::where('name', 'superadmin')->where('guard_name', 'web')->first();
                $user->assignRole($role);
            } elseif ($user->email === 'admin@gmail.com') {
                $role = Role::where('name', 'admin')->where('guard_name', 'web')->first();
                $user->assignRole($role);
            } elseif ($user->email === 'manager@gmail.com') {
                $role = Role::where('name', 'manager')->where('guard_name', 'web')->first();
                $user->assignRole($role);
            } elseif ($user->email === 'test@gmail.com') {
                $role = Role::where('name', 'distributor')->where('guard_name', 'web')->first();
                $user->assignRole($role);
            } elseif ($user->email === 'manager@example.com') {
                $role = Role::where('name', 'manager')->where('guard_name', 'web')->first();
                $user->assignRole($role);
            } elseif ($user->email === 'retailer@gmail.com') {
                $role = Role::where('name', 'retailer')->where('guard_name', 'web')->first();
                $user->assignRole($role);
            }
        }
    }
}