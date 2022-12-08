<?php

namespace Zzzul\Generator\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\AboutCommand;
use Zzzul\Generator\Commands\{SetSidebarType, PublishAllFiles};

class GeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if(!app()->isProduction()){
            $this->loadRoutesFrom(__DIR__.'/../Routes/generator.php');
            $this->loadViewsFrom(__DIR__ . '/../Resources/views/generators', 'generator');

            // views
            $this->publishes([
                __DIR__ . '/../Resources/views/published' => resource_path('views'),
            ], 'generator-view');

            // config
            $this->publishes([
                __DIR__ . '/../Config/Full' => config_path()
            ], 'generator-config');

             // config simple
            $this->publishes([
                __DIR__ . '/../Config/Simple/generator.php' => config_path('generator.php')
            ], 'generator-config-simple');

            // Controllers
            $this->publishes([
                __DIR__ . '/../Http/Controllers/Published' => app_path('Http/Controllers')
            ], 'generator-controller');

            // Requests
            $this->publishes([
                __DIR__ . '/../Http/Requests/Published' => app_path('Http/Requests')
            ], 'generator-request');

            // Actions fortify
            $this->publishes([
                __DIR__ . '/../Actions' => app_path('Actions')
            ], 'generator-action');

            // Kernel
            $this->publishes([
                __DIR__ . '/../Http/Kernel.php' => app_path('Http/Kernel.php')
            ], 'generator-kernel');

            // Providers
            $this->publishes([
                __DIR__ . '/../Providers/Published' => app_path('Providers')
            ], 'generator-provider');

            $this->publishes([
                __DIR__ . '/../Providers/Published/Simple' => app_path('Providers')
            ], 'generator-view-provider');

            // Migrations
            $this->publishes([
                __DIR__ . '/../Database/Migrations' => database_path('migrations')
            ], 'generator-migration');

            // Seeder
            $this->publishes([
                __DIR__ . '/../Database/Seeders' => database_path('seeders')
            ], 'generator-seeder');

            // Model
            $this->publishes([
                __DIR__ . '/../Models/User.php' => app_path('Models/User.php')
            ], 'generator-model');

            // asset/mazer template
            $this->publishes([
                __DIR__ . '/../../assets' => public_path('mazer'),
            ], 'generator-assets');

            AboutCommand::add('Generator', fn () => ['Version' => '0.2.0']);

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
}
