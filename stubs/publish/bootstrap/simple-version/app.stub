<?php

use App\Providers\ViewComposerServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(path: __DIR__))
    ->withRouting(
        // api: __DIR__ . '/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders(providers: [
        ViewComposerServiceProvider::class,
    ])
    ->withMiddleware(callback: function (Middleware $middleware): void {
        //
    })
    ->withExceptions(using: function (Exceptions $e): void {
        //
    })->create();
