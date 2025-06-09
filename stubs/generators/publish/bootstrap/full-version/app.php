<?php

use App\Providers\FortifyServiceProvider;
use App\Providers\ViewComposerServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(path: __DIR__))
    ->withRouting(
        // api: __DIR__ . '/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders(providers: [
        FortifyServiceProvider::class,
        ViewComposerServiceProvider::class,
    ])
    ->withMiddleware(callback: function (Middleware $middleware): void {
        $middleware->alias(aliases: [
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(using: function (Exceptions $e): void {
        //
    })->create();
