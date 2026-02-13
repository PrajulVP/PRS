<?php

namespace SreyasAS\UnSlayShell;

use Illuminate\Support\ServiceProvider;

class UnSlayShellServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Load Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'unslay-shell');

        // Publish Config
        $this->publishes([
            __DIR__ . '/../config/unslay-shell.php' => config_path('unslay-shell.php'),
        ], 'unslay-shell-config');

        // Publish Views (optional, if user wants to customize)
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/unslay-shell'),
        ], 'unslay-shell-views');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Merge Config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/unslay-shell.php',
            'unslay-shell'
        );
    }
}
