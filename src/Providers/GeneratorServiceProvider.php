<?php

namespace EvdigiIna\Generator\Providers;

use Illuminate\Support\ServiceProvider;
use EvdigiIna\Generator\Commands\{SetSidebarType, PublishAllFiles};
use Generator;

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
        $this->loadRoutesFrom(__DIR__ . '/../../routes/generator.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'generator');
        $this->mergeConfigFrom(__DIR__ . '/../../stubs/generators/publish/config/full-version/generator.php', 'generator');

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
            __DIR__ . '/../../database/migrations' => database_path('migrations')
        ], 'generator-migration');

        // Seeder
        $this->publishes([
            __DIR__ . '/../../database/seeders' => database_path('seeders')
        ], 'generator-seeder');

        // Model
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/models/full-version/User.php' => app_path('Models/User.php')
        ], 'generator-model-full');

        // Model
        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/models/simple-version/User.php' => app_path('Models/User.php')
        ], 'generator-model-simple');

        // asset/mazer template
        $this->publishes([
            __DIR__ . '/../../assets' => public_path('mazer'),
        ], 'generator-assets');

        $this->publishes([
            __DIR__ . '/../../stubs/generators/publish/utils' => app_path('Generators')
        ], 'generator-utils');

        if (class_exists(\Illuminate\Foundation\Console\AboutCommand::class)) {
            \Illuminate\Foundation\Console\AboutCommand::add('Generator', fn () => [
                'Version' => '0.3.0',
                'Source' => 'https://github.com/evdigiina/generator',
                'Docs' => 'https://evdigi-ina.github.io/generator-docs',
                'About' =>  'Package for building basic CRUD, specially for your main data.'
            ]);
        }

        $this->app->bind('generator', function () {
            return new Generator();
        });

        if ($this->app->runningInConsole()) $this->commands([PublishAllFiles::class]);

        $this->commands([SetSidebarType::class]);
    }
}
