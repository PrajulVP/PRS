<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenRecoveryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Fix for rigid hosting platforms stripping Authorization header
        if ($request->hasHeader('X-Authorization')) {
            $request->headers->set('Authorization', $request->header('X-Authorization'));
        }

        return $next($request);
    }
}
