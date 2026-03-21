<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// 1. Check if roles exist
$roleExists = DB::table('roles')->where('name', 'retailer')->where('guard_name', 'web')->exists();
echo "Role 'retailer' (web) exists: " . ($roleExists ? 'Yes' : 'No') . PHP_EOL;

// 2. Simulate API context (Spatie uses the current auth guard by default)
Auth::shouldUse('api');
echo "Current Guard: " . Auth::getDefaultDriver() . PHP_EOL;

try {
    // 3. Create a test user
    $email = 'test_api_role_' . time() . '@example.com';
    $user = User::create([
        'name' => 'Test API Role',
        'email' => $email,
        'password' => bcrypt('password'),
        'status' => 'inactive',
        'role' => 'retailer', // Explicitly provide the required role field
    ]);

    echo "User created. Attempting to assign 'retailer' role..." . PHP_EOL;

    // 4. Assign role
    $user->assignRole('retailer');
    echo "Role assigned successfully!" . PHP_EOL;

    // 5. Check if user has role
    $hasRole = $user->hasRole('retailer');
    echo "hasRole('retailer') check: " . ($hasRole ? 'True (Success)' : 'False (Failed)') . PHP_EOL;

    // Clean up
    $user->delete();
    echo "Cleanup: User deleted." . PHP_EOL;

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
