<?php

namespace EvdigiIna\Generator\Providers;

use Illuminate\Support\ServiceProvider;
use EvdigiIna\Generator\Commands\{SetSidebarType, PublishAllFiles};

class GeneratorServiceProvider extends ServiceProvider
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
        $this->loadRoutesFrom(__DIR__ . '/../Routes/generator.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views/generators', 'generator');

        // views
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/views/' => resource_path('views'),
        ], 'generator-view');

        // config
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/config/full-version' => config_path()
        ], 'generator-config');

        // config simple
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/config/simple-version/generator.php' => config_path('generator.php')
        ], 'generator-config-simple');

        // Controllers
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/controllers' => app_path('Http/Controllers')
        ], 'generator-controller');

        // Requests
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/requests' => app_path('Http/Requests')
        ], 'generator-request');

        // Actions fortify
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/fortify' => app_path('Actions/Fortify')
        ], 'generator-action');

        // Kernel
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/Kernel.php' => app_path('Http/Kernel.php')
        ], 'generator-kernel');

        // Providers
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/providers/full-version' => app_path('Providers')
        ], 'generator-provider');

        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/simple-version' => app_path('Providers')
        ], 'generator-view-provider');

        // Migrations
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/database/migrations' => database_path('migrations')
        ], 'generator-migration');

        // Seeder
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/database/seeders' => database_path('seeders')
        ], 'generator-seeder');

        // Model
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/models/User.php' => app_path('Models/User.php')
        ], 'generator-model');

        // asset/mazer template
        $this->publishes([
            __DIR__ . '/../../assets' => public_path('mazer'),
        ], 'generator-assets');

        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/utils/helper.php' => app_path('Generators/helper.php'),
            __DIR__ . '/../../stubs/generators/publish/utils/GeneratorUtils.php' => app_path('Generators/GeneratorUtils.php')
        ], 'generator-utils');

        // Illuminate\Foundation\Console\AboutCommand::add('Generator', fn () => ['Version' => '0.2.0']);

        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishAllFiles::class
            ]);
        }

        $this->commands([
            SetSidebarType::class
        ]);
    }
}
