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
        Log::info('AuthMiddleware: User authenticated: ' . (string) Auth::guard('admin')->check());

        if (Auth::guard('admin')->check()) {
          
            return $next($request);
        }

        return redirect()->route('admin.login')->with('error', 'You are not logged in.');
    }

}
