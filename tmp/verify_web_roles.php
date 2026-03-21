<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// 1. Simulate Web context
Auth::shouldUse('web');
echo "Current Guard: " . Auth::getDefaultDriver() . PHP_EOL;

try {
    // 2. Create a test user
    $email = 'test_web_role_' . time() . '@example.com';
    $user = User::create([
        'name' => 'Test Web Role',
        'email' => $email,
        'password' => bcrypt('password'),
        'status' => 'inactive',
        'role' => 'retailer',
    ]);

    echo "User created. Attempting to assign 'retailer' role..." . PHP_EOL;

    // 3. Assign role
    $user->assignRole('retailer');
    echo "Role assigned successfully!" . PHP_EOL;

    // 4. Check if user has role
    $hasRole = $user->hasRole('retailer');
    echo "hasRole('retailer') check: " . ($hasRole ? 'True (Success)' : 'False (Failed)') . PHP_EOL;

    // Clean up
    $user->delete();
    echo "Cleanup: User deleted." . PHP_EOL;

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
