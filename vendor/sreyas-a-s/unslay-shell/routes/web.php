<?php

use Illuminate\Support\Facades\Route;
use SreyasAS\UnSlayShell\Http\Controllers\TerminalController;

Route::group([
    'prefix' => config('unslay-shell.route_prefix', 'unslay-terminal'),
    'middleware' => config('unslay-shell.middleware', ['web']),
], function () {
    if (config('unslay-shell.enabled', true)) {
        Route::get('/', [TerminalController::class, 'index'])->name('unslay-shell.index');
        Route::post('/login', [TerminalController::class, 'login'])->name('unslay-shell.login');
        Route::post('/execute', [TerminalController::class, 'execute'])->name('unslay-shell.execute');
        Route::post('/autocomplete', [TerminalController::class, 'autocomplete'])->name('unslay-shell.autocomplete');
    }
});
