<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$sm = \App\Models\SalesManager::create(['user_id' => 1, 'address' => 'X', 'pincode' => '123', 'status' => 'active', 'monthly_target' => 0]); 
$fsUser = \App\Models\User::create(['name' => 'FS', 'email' => 'fs@gmail.com', 'password' => \Illuminate\Support\Facades\Hash::make('12345'), 'role' => 'fieldstaff', 'status' => 'active']); 
$fsUser->assignRole('fieldstaff'); 
\App\Models\FieldStaff::create(['user_id' => $fsUser->id, 'sales_manager_id' => $sm->id, 'address' => 'X', 'pincode' => '123', 'status' => 'active']);
echo "Created FS user.\n";
