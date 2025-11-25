<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show login view (used for /admin/login and /login).
     */
    public function showLogin()
    {
        // Check if the user is authenticated under the default web guard
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard'); // Redirect to the dashboard
        }

        return view('auth.login'); // Show login form if not authenticated
    }

    /**
     * Web login (session-based). Accepts email + password.
     * Works for web form POST to /admin/login or /login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if user exists
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            // Email not found
            return back()->withErrors(['email' => 'Email does not exist'])->withInput();
        }

        // Block inactive users BEFORE login, except superadmin/admin
        if (!in_array($user->role, ['superadmin', 'admin']) && $user->status === 'inactive') {
            return back()->withErrors(['inactive' => 'Your account is inactive. Please contact admin.'])
                        ->withInput();
        }

        // Check password manually
        if (!\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password'])->withInput();
        }

        // Attempt login
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }


    /**
     * Dashboard entrypoint. Use a single blade with role-based conditional UI,
     * or redirect to role-specific route if you prefer.
     */
    public function dashboard(Request $request)
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $role = $user->getRoleNames()->first(); // Assuming a user has only one role for dashboard purposes

            $dashboardView = 'dashboard.admin'; // Default dashboard

            switch ($role) {
                case 'superadmin':
                    $dashboardView = 'dashboard.superadmin';
                    break;
                case 'admin':
                    $dashboardView = 'dashboard.admin';
                    break;
                case 'salesmanager':
                    $dashboardView = 'dashboard.salesmanager';
                    break;
                case 'distributor':
                    $dashboardView = 'dashboard.distributor';
                    break;
                case 'fieldstaff':
                    $dashboardView = 'dashboard.fieldstaff';
                    break;
                case 'retailer':
                    $dashboardView = 'dashboard.retailer';
                    break;
                default:
                    // Handle unknown roles or redirect to a default view
                    $dashboardView = 'dashboard.admin';
                    break;
            }

            return view($dashboardView, [
                'user' => $user,
                'role' => $role,
            ]);
        }

        return redirect()->route('login');
    }

    /**
     * Web logout (session).
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
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