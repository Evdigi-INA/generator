<?php

namespace EvdigiIna\Generator\Providers;

use EvdigiIna\Generator\Commands\PublishAllFiles;
use EvdigiIna\Generator\Commands\PublishApiCommand;
use EvdigiIna\Generator\Commands\PublishImageServiceV2Command;
use EvdigiIna\Generator\Commands\PublishUtilsCommand;
use EvdigiIna\Generator\Commands\SetSidebarType;
use EvdigiIna\Generator\Generator;
use Illuminate\Support\ServiceProvider;

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
        $this->loadRoutesFrom(__DIR__.'/../../routes/generator.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'generator');
        $this->mergeConfigFrom(__DIR__.'/../../stubs/generators/publish/config/full-version/generator.php', 'generator');

        // views
        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/views/' => resource_path('views'),
        ], 'generator-view');

        // config
        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/config/full-version' => config_path(),
        ], 'generator-config');

        // config simple
        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/config/simple-version/generator.php' => config_path('generator.php'),
        ], 'generator-config-simple');

        // Controllers
        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/controllers/ProfileController.php' => app_path('Http/Controllers/ProfileController.php'),
        ], 'generator-controller');

        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/controllers/RoleAndPermissionController.php' => app_path('Http/Controllers/RoleAndPermissionController.php'),
        ], 'generator-controller');

        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/controllers/UserController.php' => app_path('Http/Controllers/UserController.php'),
        ], 'generator-controller');

        // Requests
        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/requests/Roles' => app_path('Http/Requests/Roles'),
        ], 'generator-request-role');

        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/requests/Users' => app_path('Http/Requests/Users'),
        ], 'generator-request-user');

        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/requests/Auth' => app_path('Http/Requests/Auth'),
        ], 'generator-request-api');

        // API Controller
        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/controllers/api' => app_path('Http/Controllers/Api'),
        ], 'generator-controller-api');

        // Role and user resources API
        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/resources' => app_path('Http/Resources'),
        ], 'generator-role-user-resource-api');

        // Actions fortify
        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/fortify' => app_path('Actions/Fortify'),
        ], 'generator-action');

        // Providers
        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/providers/full-version' => app_path('Providers'),
        ], 'generator-provider');

        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/providers/simple-version/' => app_path('Providers'),
        ], 'generator-view-provider');

        // Migrations
        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'generator-migration');

        // Seeder
        $this->publishes([
            __DIR__.'/../../database/seeders' => database_path('seeders'),
        ], 'generator-seeder');

        // Model
        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/models/User.php' => app_path('Models/User.php'),
        ], 'generator-model');

        // asset/mazer template
        $this->publishes([
            __DIR__.'/../../assets' => public_path('mazer'),
        ], 'generator-assets');

        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/utils' => app_path('Generators'),
        ], 'generator-utils');

        $this->publishes(paths: [
            __DIR__.'/../../stubs/generators/publish/utils/Interfaces/ImageServiceInterfaceV2.php' => app_path('Generators/Interfaces/ImageServiceInterfaceV2.php'),
            __DIR__.'/../../stubs/generators/publish/utils/Services/ImageServiceV2.php' => app_path('Generators/Services/ImageServiceV2.php'),
            __DIR__.'/../../stubs/generators/publish/utils/ImageUploadOption.php' => app_path('Generators/ImageUploadOption.php'),
        ], groups: 'image-service-v2');

        // bootstrap app (laravel 11)
        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/bootstrap/full-version/app.php' => base_path('bootstrap/app.php'),
        ], 'bootstrap-app-full');

        $this->publishes([
            __DIR__.'/../../stubs/generators/publish/bootstrap/simple-version/app.php' => base_path('bootstrap/app.php'),
        ], 'bootstrap-app-simple');

        if (class_exists(\Illuminate\Foundation\Console\AboutCommand::class)) {
            \Illuminate\Foundation\Console\AboutCommand::add('Generator', fn () => [
                'Version' => '0.4.0',
                'Source' => 'https://github.com/evdigiina/generator',
                'Docs' => 'https://zzzul.github.io/generator-docs-next/',
                'About' => 'Automate CRUD, Focus on Core Features.',
            ]);
        }

        $this->app->bind('generator', fn () => new Generator);

        if ($this->app->runningInConsole()) {
            $this->commands([PublishAllFiles::class]);
        }

        $this->commands([SetSidebarType::class, PublishApiCommand::class, PublishUtilsCommand::class, PublishImageServiceV2Command::class]);
    }
}
