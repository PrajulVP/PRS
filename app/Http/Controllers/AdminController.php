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
        $admin = Auth::guard('web')->user();
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
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // API login
        if ($request->expectsJson()) {
            if (!$token = Auth::guard('admin')->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return $this->respondWithToken($token);
        }

       // Web login
        $admin = Admin::where('email', $request->email)->first();
        if (!$admin || !password_verify($request->password, $admin->password)) {
            return back()->withErrors(['email' => 'Invalid credentials']);
        }

        // Use **admin guard** for web session
        Auth::guard('admin')->login($admin);

        return redirect()->route('admin.dashboard');
    }

    // Logout
    public function logout(Request $request)
    {
        if ($request->expectsJson()) {
            Auth::guard('admin')->logout();
            return response()->json(['message' => 'Successfully logged out']);
        }

        Auth::guard('web')->logout();
        return redirect()->route('login')->with('success', 'Logged out successfully');
    }

    // JWT token response
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::guard('admin')->factory()->getTTL() * 60
        ]);
    }
}
