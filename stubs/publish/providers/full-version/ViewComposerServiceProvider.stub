<?php

namespace App\Providers;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer(views: ['users.create', 'users.edit'], callback: fn (ViewContract $view) => $view->with(
            key: 'roles',
            value: Role::select(columns: ['id', 'name'])->get()
        ));
    }
}
