<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // Dashboard
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            // API request → JWT guard
            $admin = Auth::guard('admin')->user();
            return response()->json([
                'message' => 'Welcome to admin dashboard',
                'admin' => $admin
            ]);
        }

        // Web request → session guard
        $admin = Auth::guard('admin')->user();
        return view('admin.index', compact('admin'));
    }

    // Show web login form
    public function showLogin()
    {
        return view('admin.login');
    }

    // Handle login (web + API)
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password'); 
        if (Auth::guard('admin')->attempt($credentials)) {
            if ($request->expectsJson()) {
                // API request → generate JWT token
                $token = auth('admin-api')->attempt($credentials);
                return $this->respondWithToken($token);
            }
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }



    // Logout
    public function logout(Request $request)
    {
        // Logout from admin guard
        Auth::guard('admin')->logout();

        // In case of AJAX/JSON request, return JSON response
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Logged out successfully']);
        }

        // For normal web request, redirect to admin login
        return redirect()->route('admin.login')->with('success', 'Logged out successfully');
    }

    // JWT token response
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('admin-api')->factory()->getTTL() * 60
        ]);
    }
}
