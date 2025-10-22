<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function index()
    {
        dd("hi");
        return view('admin.index');
    }

    public function login(Request $request)
    {
        // If GET request → show form for web
        if ($request->isMethod('get')) {
            return view('admin.login');
        }

        // Validate POST data
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            Log::info('Admin login failed for email: ' . $request->email . ' - Invalid credentials');
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
            }
            return back()->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $user = Auth::user();
        Log::info('Admin login successful for user ID: ' . $user->id);
        // API login → return token
        if ($request->expectsJson()) {
           
            return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'Login successful.',
            'redirect_url' => route('admin.dashboard') 
            ]);
            
        }

        // Web login → redirect
        Log::info('Attempting to redirect admin user ID: ' . $user->id . ' to dashboard');
        return redirect()->route('admin.dashboard');
    }



    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/'); // Redirect to home or login page
    }
}
