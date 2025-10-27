<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Show login view (used for /admin/login and /login).
     */
    public function showLogin()
    {
        return view('auth.login'); // create resources/views/auth/login.blade.php
    }

    /**
     * Web login (session-based). Accepts email + password.
     * Works for web form POST to /admin/login or /login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // If you want to redirect to a role-specific url:
            return redirect()->route('dashboard');
        }

        // back with error
        return back()->withErrors(['email' => 'The provided credentials are incorrect.']);
    }

    /**
     * Dashboard entrypoint. Use a single blade with role-based conditional UI,
     * or redirect to role-specific route if you prefer.
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Option: redirect to role-specific route
        switch ($user->role) {
            case 'superadmin':
                return redirect()->route('dashboard.superadmin');
            case 'admin':
                return redirect()->route('dashboard.admin');
            case 'manager':
                return redirect()->route('dashboard.manager');
            case 'distributor':
                return redirect()->route('dashboard.distributor');
            case 'fieldstaff':
                return redirect('/dashboard/fieldstaff');
            case 'retailer':
                return redirect()->route('dashboard.retailer');
            default:
                // fallback to generic dashboard view
                return view('dashboard', compact('user'));
        }
    }

    /**
     * Web logout (session).
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
    }

    /**
     * API: Login using JWT. Returns token + user.
     * POST /api/login  { email, password }
     */
    public function apiLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        $user = auth()->user(); // JWTAuth set the user

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60 ?? null,
            'user'         => $user,
        ]);
    }

    /**
     * API: get profile (protected)
     */
    public function apiProfile(Request $request)
    {
        return response()->json(auth()->user());
    }

    /**
     * API: logout — invalidate token
     */
    public function apiLogout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }
            return response()->json(['message' => 'Token invalidated. Logged out.']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout, token invalidation error.'], 500);
        }
    }
}
