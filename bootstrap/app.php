<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/admin/login',
        ]);

        $middleware->api(append: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        ]);

        $middleware->alias([
            'auth.admin' => \App\Http\Middleware\AuthMiddleware::class, // ✅ custom admin guard
            'jwt.auth'   => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
            'admin'      => \App\Http\Middleware\AdminMiddleware::class, // optional
        ]);

        $middleware->encryptCookies(except: [
            'jwt'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
