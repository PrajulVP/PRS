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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;

            // Web request → redirect to dashboard
            if (!$request->wantsJson()) {
                return redirect()->route('admindashboard');
            }

            // API request → return JSON (Flutter/App)
            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => $token,
                'message' => 'Login successful.'
            ]);
             // ✅ If request is from normal Web Browser
            return redirect()->route('admin.dashboard');
        }

        // Failed login
        if (!$request->wantsJson()) {
            return back()->withErrors([
                'email' => 'Invalid credentials.'
            ])->withInput();
        }

        return response()->json([
            'success' => false,
            'message' => 'The provided credentials do not match our records.',
        ], 401);
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
