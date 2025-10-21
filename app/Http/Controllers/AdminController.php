<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboardData()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Welcome to the admin dashboard!'
            ]
        ]);
    }

    public function showLogin()
    {
       
        return view('admin/login');
    }

    public function login(Request $request)
    {
        // If GET request → show form for web
        if ($request->isMethod('get')) {
            return view('admin/login');
        }

        // Validate POST data
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
            }
            return back()->withErrors(['message' => 'Invalid Credentials']);
        }

        $user = Auth::user();

        // API login → return token
        if ($request->expectsJson()) {
            $token = $user->createToken('authToken')->plainTextToken;
            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => $token,
                'message' => 'Login successful.'
            ]);
        }

        // Web login → redirect
        return redirect()->route('admindashboard');
    }



    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.'
        ]);
    }
}
