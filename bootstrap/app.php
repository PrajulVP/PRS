<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ✅ Exclude CSRF ONLY for login API
        $middleware->validateCsrfTokens(except: [
            'api/admin/login',
        ]);

        // ✅ Add cookie support to API (if needed for JWT in cookies)
        $middleware->api(append: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        ]);

        // ✅ Define custom middleware aliases
        $middleware->alias([
            'auth.admin' => \App\Http\Middleware\AuthMiddleware::class, // custom admin session guard
            // 'jwt.auth'   => \Tymon\JWTAuth\Http\Middleware\Authenticate::class, // JWT guard
            'admin'      => \App\Http\Middleware\AdminMiddleware::class, // optional view-only redirect guard
            'role'       => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // ✅ Prevent "jwt" cookie from being encrypted (optional)
        $middleware->encryptCookies(except: [
            'jwt',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }
        });
    })
    ->create();
