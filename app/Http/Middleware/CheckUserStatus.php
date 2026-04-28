<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check web guard
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if (!in_array($user->role, ['superadmin', 'admin']) && $user->status === 'inactive') {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Your account is inactive.', 'redirect' => route('login')], 403);
                }

                return redirect()->route('login')->withErrors(['inactive' => 'Your account is inactive. Please contact admin.']);
            }
        }

        // Check api guard
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            if (!in_array($user->role, ['superadmin', 'admin']) && $user->status === 'inactive') {
                // If using JWT, we might want to invalidate the token, but just returning 403 works for the client
                try {
                    auth('api')->logout();
                } catch (\Exception $e) {
                    // Ignore exception if token is already invalid
                }
                return response()->json(['error' => 'Your account is inactive. Please contact admin.', 'status' => 'inactive'], 403);
            }
        }

        return $next($request);
    }
}
