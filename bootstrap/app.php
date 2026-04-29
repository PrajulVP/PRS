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
            'api/*',
        ]);

        // ✅ Global intercept to fix rigid hosting platforms stripping Authorization
        $middleware->append(\App\Http\Middleware\TokenRecoveryMiddleware::class);

        // ✅ Force JSON response for API routes
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        // ✅ Add cookie support to API (if needed for JWT in cookies)
        $middleware->api(append: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \App\Http\Middleware\CheckUserStatus::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\CheckUserStatus::class,
        ]);

        // ✅ Define custom middleware aliases
        $middleware->alias([
            'auth.admin' => \App\Http\Middleware\AuthMiddleware::class, // custom admin session guard
            // 'jwt.auth'   => \Tymon\JWTAuth\Http\Middleware\Authenticate::class, // JWT guard
            'admin'      => \App\Http\Middleware\AdminMiddleware::class, // optional view-only redirect guard
            'role'       => \App\Http\Middleware\RoleMiddleware::class,
            'device.binding' => \App\Http\Middleware\DeviceBindingMiddleware::class,
        ]);

        // ✅ Prevent "jwt" cookie from being encrypted (optional)
        $middleware->encryptCookies(except: [
            'jwt',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
