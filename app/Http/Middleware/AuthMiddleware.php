<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('AuthMiddleware: Request Path: ' . $request->path());
        Log::info('AuthMiddleware: Session ID: ' . $request->session()->getId());
        Log::info('AuthMiddleware: Auth::guard(\'admin\')->check(): ' . (string) Auth::guard('admin')->check());

        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            Log::info('AuthMiddleware: User authenticated. User ID: ' . $user->id . ', Role: ' . $user->role);
            return $next($request);
        }

        Log::info('AuthMiddleware: User not authenticated. Redirecting to admin.login.');
        return redirect()->route('login')->with('error', 'You are not logged in.');
    }

}
