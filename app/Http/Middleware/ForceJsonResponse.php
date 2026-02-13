<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Fix for missing Authorization header on some shared hosting setups
        if (!$request->headers->has('Authorization')) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $request->headers->set('Authorization', $_SERVER['HTTP_AUTHORIZATION']);
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $request->headers->set('Authorization', $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
            }
        }

        $request->headers->set('Accept', 'application/json');
        return $next($request);
    }
}
