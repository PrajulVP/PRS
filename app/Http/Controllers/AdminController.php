<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $targetValue = 100;          // Example → get from DB instead
        $achievement = 40;           // Example → get from DB instead
        return view('admin.index', compact('targetValue', 'achievement'));
    }

    // Show login form (web)
    public function showLogin()
    {
        return view('admin.login');
    }

    // Handle login (web + API)
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $admin = Auth::guard('admin')->user();

            // Redirect per admin type
            return match ($admin->role ?? 'admin') {
                'superadmin'   => redirect()->route('superadmin.dashboard'),
                'manager'      => redirect()->route('manager.dashboard'),
                'distributor'  => redirect()->route('distributor.dashboard'),
                'retailer'     => redirect()->route('retailer.dashboard'),
                'fieldstaff'   => redirect()->route('fieldstaff.dashboard'),
                default        => redirect()->route('admin.dashboard'),
            };
            
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Logged out successfully']);
        }

        return redirect()->route('admin.login')->with('success', 'Logged out successfully');
    }
}
