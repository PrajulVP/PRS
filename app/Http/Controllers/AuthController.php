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

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="PRS API",
 *      description="API documentation for the PRS application.",
 *      @OA\Contact(
 *          email="support@prs.com"
 *      )
 * )
 */
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
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authenticate user and get JWT token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Get authenticated user profile",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile data"
     *     )
     * )
     */
    public function apiProfile(Request $request)
    {
        return response()->json(auth()->user());
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user and invalidate token",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out"
     *     )
     * )
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