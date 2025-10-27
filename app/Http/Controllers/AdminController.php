<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // Show login form (web)
    public function showLogin()
    {
        return view('admin.login');
    }

    // Handle login (web + API)
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Redirect based on role
            return redirect()->route('dashboard.' . $user->role);
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Logged out successfully']);
        }

        return redirect()->route('admin.login')->with('success', 'Logged out successfully');
    }
}
