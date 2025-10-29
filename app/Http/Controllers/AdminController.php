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
        return view('admin.dashboard', compact('targetValue', 'achievement'));
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
