<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Terminal Route Prefix
    |--------------------------------------------------------------------------
    |
    | The path where the terminal will be accessible.
    |
    */
    'route_prefix' => 'unslay-terminal',

    /*
    |--------------------------------------------------------------------------
    | Terminal Password
    |--------------------------------------------------------------------------
    |
    | The password required to access the terminal.
    | It is highly recommended to set this in your .env file.
    |
    */
    'password' => env('UNSLAY_SHELL_PASSWORD', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to apply to the terminal routes.
    | The 'web' middleware is usually required for sessions.
    |
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Master switch to enable/disable the terminal.
    |
    */
    'enabled' => env('UNSLAY_SHELL_ENABLED', true),
];
