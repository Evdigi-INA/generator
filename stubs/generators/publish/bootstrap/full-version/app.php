<?php

use App\Providers\{FortifyServiceProvider, ViewComposerServiceProvider};
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions,Middleware};
use Spatie\Permission\Middleware\{RoleMiddleware, PermissionMiddleware,RoleOrPermissionMiddleware};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        FortifyServiceProvider::class,
        ViewComposerServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
