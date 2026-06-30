<?php

namespace Workbench\App\Providers;

use App\View\Components\Breadcrumb;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
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
        Blade::component('breadcrumb', Breadcrumb::class);
    }
}
