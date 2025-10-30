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
        // Check if any of the custom guards are authenticated
        if (Auth::guard('superadmin')->check() ||
            Auth::guard('admin')->check() ||
            Auth::guard('manager')->check() ||
            Auth::guard('distributor')->check() ||
            Auth::guard('fieldstaff')->check() ||
            Auth::guard('retailer')->check()) {
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
            'email'    => ['required','email'],
            'password' => ['required'],
        ]);

        // Attempt to find the user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'This email is not registered.']);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        // Determine the guard based on the user's primary role
        // Assuming the first role returned by getRoleNames() is the primary role and matches a guard name.
        $guard = $user->getRoleNames()->first();

        // If no role is assigned, or the role doesn't match a guard, authentication cannot proceed.
        if (!$guard) {
            return back()->withErrors(['email' => 'No role assigned to this user, or role does not match a guard.']);
        }

        if (Auth::guard($guard)->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        // Fallback error if authentication fails for other reasons (e.g., guard misconfiguration)
        return back()->withErrors(['email' => 'Authentication failed. Please try again.']);
    }

    /**
     * Dashboard entrypoint. Use a single blade with role-based conditional UI,
     * or redirect to role-specific route if you prefer.
     */
    public function dashboard(Request $request)
    {
        // Determine which guard is active and logged in
        $guards = ['superadmin', 'admin', 'manager', 'distributor', 'fieldstaff', 'retailer'];

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // ✅ Option 1: Single dashboard with role-based sections
                return view('admin.dashboard', [
                    'user' => $user,
                    'role' => $guard,
                ]);
            }
        }
                
        // If no role authenticated, go back to login
        return redirect()->route('login');
    }

    /**
     * Web logout (session).
     */
    public function logout(Request $request)
    {
        $guards = ['superadmin', 'admin', 'manager', 'distributor', 'fieldstaff', 'retailer', 'web'];
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

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
