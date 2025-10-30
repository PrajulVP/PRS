<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Ensure a user is authenticated before checking their role
        if (! $request->user()) {
            // If no user is authenticated, deny access or redirect to login
            // For API requests, return a 401 Unauthorized response
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            // For web requests, redirect to login
            return redirect()->route('login'); // Or a general login route
        }

        if (!in_array($request->user()->role, $roles)) {
            // If the user does not have the required role, deny access
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
