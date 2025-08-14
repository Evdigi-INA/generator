<?php

namespace EvdigiIna\Generator\Providers;

use EvdigiIna\Generator\Commands\PublishAllFiles;
use EvdigiIna\Generator\Commands\PublishApiCommand;
use EvdigiIna\Generator\Commands\PublishImageServiceV2Command;
use EvdigiIna\Generator\Commands\PublishUtilsCommand;
use EvdigiIna\Generator\Commands\SetSidebarType;
use EvdigiIna\Generator\Generator;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;

class GeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(abstract: 'generator', concrete: fn(): Generator => new Generator);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadBaseResources();
        $this->registerPublishGroups();
        $this->registerAboutInfo();
        $this->registerCommands();
    }

    /**
     * Load the base resources, like routes, views and config.
     *
     * This method is called in the boot method of this service provider.
     */
    protected function loadBaseResources(): void
    {
        $this->loadRoutesFrom(path: __DIR__ . '/../../routes/generator.php');
        $this->loadViewsFrom(path: __DIR__ . '/../../views', namespace: 'generator');
        $this->mergeConfigFrom(path: __DIR__ . '/../../config/generator.php', key: 'generator');
    }

    /**
     * Register publish groups.
     *
     * This method registers all the publish groups that are provided by the generator.
     * The publish groups are grouped into several categories: views, configs, controllers, requests, api resources, fortify actions, providers, database resources, models, assets, and utils.
     */
    protected function registerPublishGroups(): void
    {
        $this->publishViews();
        $this->publishConfigs();
        $this->publishControllers();
        $this->publishRequests();
        $this->publishApiResources();
        $this->publishFortifyActions();
        $this->publishProviders();
        $this->publishDatabaseResources();
        $this->publishModels();
        $this->publishAssets();
        $this->publishUtils();
        $this->publishBootstrapFiles();
    }

    /**
     * Publish views.
     *
     * This method publishes the views for the generator.
     * The views are grouped into several categories: auth, components, layouts, roles, users, and dashboard & profile.
     */
    protected function publishViews(): void
    {
        $this->publishes(paths: [
            // Auth views
            ...$this->mapStubsToPaths(category: 'views/auth', files: [
                'confirm-password.blade',
                'forgot-password.blade',
                'login.blade',
                'register.blade',
                'reset-password.blade',
                'two-factor-challenge.blade',
            ]),

            // Component views
            ...$this->mapStubsToPaths(category: 'views/components', files: [
                'breadcrumb.blade',
                'alert.blade',
            ]),

            // Layout views
            ...$this->mapStubsToPaths(category: 'views/layouts', files: [
                'app.blade',
                'auth.blade',
                'footer.blade',
                'header.blade',
                'sidebar.blade',
            ]),

            // Role views
            ...$this->mapStubsToPaths(category: 'views/roles', files: [
                'create.blade',
                'edit.blade',
                'index.blade',
                'show.blade',
                'include/form.blade',
                'include/action.blade',
            ]),

            // User views
            ...$this->mapStubsToPaths(category: 'views/users', files: [
                'create.blade',
                'edit.blade',
                'index.blade',
                'show.blade',
                'include/form.blade',
                'include/action.blade',
            ]),

            // Dashboard & profile
            __DIR__ . '/../../stubs/publish/views/dashboard.blade.stub' => resource_path(path: 'views/dashboard.blade.php'),
            __DIR__ . '/../../stubs/publish/views/profile.blade.stub' => resource_path(path: 'views/profile.blade.php'),
        ], groups: 'generator-views');
    }

    /**
     * Publish config files.
     *
     * This method publish the config files that are used to configure the framework.
     * The full version of config files is used when the generator is configured to use the full version.
     * The simple version of config files is used when the generator is configured to use the simple version.
     */
    protected function publishConfigs(): void
    {
        // Full config
        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'config/full-version', files: [
                'fortify',
                'generator',
                'permission',
            ]),
        ], groups: 'generator-full-config');

        // Simple config
        $this->publishes(paths: [
            __DIR__ . '/../../stubs/publish/config/simple-version/generator.stub' => config_path(path: 'generator.php'),
        ], groups: 'generator-simple-config');
    }

    /**
     * Publish controllers.
     *
     * This method publishes the controllers for the generator.
     * The controllers are grouped into two categories: web and API.
     *
     * The web controllers are:
     * - `ProfileController`
     * - `RoleAndPermissionController`
     * - `UserController`
     *
     * The API controllers are:
     * - `AuthController`
     * - `RoleAndPermissionController`
     * - `UserController`
     */
    protected function publishControllers(): void
    {
        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'controllers', files: [
                'ProfileController',
                'RoleAndPermissionController',
                'UserController',
            ]),
        ], groups: 'generator-controller');

        // API Controllers
        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'controllers/api', files: [
                'AuthController',
            ]),
        ], groups: 'generator-controller-api');

        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'controllers/api', files: [
                'RoleAndPermissionController',
                'UserController',
            ]),
        ], groups: 'generator-controller-user-role-api');

    }

    /**
     * Publish requests.
     *
     * This method publishes the requests for API controllers.
     * The requests are grouped into three categories: role, user, and auth API.
     */
    protected function publishRequests(): void
    {
        // Role requests
        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'requests/Roles', files: [
                'StoreRoleRequest',
                'UpdateRoleRequest',
            ]),
        ], groups: 'generator-request-role');

        // User requests
        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'requests/Users', files: [
                'UpdateUserRequest',
                'StoreUserRequest',
            ]),
        ], groups: 'generator-request-user');

        // Auth API requests
        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'requests/Auth', files: [
                'LoginRequest',
                'RegisterRequest',
            ]),
        ], groups: 'generator-request-auth-api');
    }

    /**
     * Publish API resources.
     *
     * This method publishes the resources for API controllers.
     */
    protected function publishApiResources(): void
    {
        $this->publishes(paths: [
            // Role resources
            ...$this->mapStubsToPaths(category: 'resources/Roles', files: [
                'RoleResource',
                'RoleCollection',
            ]),

            // User resources
            ...$this->mapStubsToPaths(category: 'resources/Users', files: [
                'UserCollection',
                'UserResource',
            ]),
        ], groups: 'generator-resource-api');
    }

    /**
     * Publish the Fortify actions stubs.
     *
     * The Fortify actions are a set of classes that are used to perform common actions such as creating a new user, resetting a user's password, updating a user's profile information, and validating passwords.
     */
    protected function publishFortifyActions(): void
    {
        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'fortify', files: [
                'CreateNewUser',
                'PasswordValidationRules',
                'ResetUserPassword',
                'UpdateUserPassword',
                'UpdateUserProfileInformation',
            ]),
        ], groups: 'generator-action');
    }

    /**
     * Publish the providers stubs.
     *
     * The full version of providers is used when the generator is configured to use the full version.
     * The simple version of providers is used when the generator is configured to use the simple version.
     */
    protected function publishProviders(): void
    {
        // Full providers
        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'providers/full-version', files: [
                'FortifyServiceProvider',
                'ViewComposerServiceProvider',
            ]),
        ], groups: 'generator-full-provider');

        // Simple provider
        $this->publishes(paths: [
            __DIR__ . '/../../stubs/publish/providers/simple-version/ViewServiceProvider.stub' => app_path(path: 'Providers/ViewServiceProvider.php'),
        ], groups: 'generator-simple-provider');
    }

    /**
     * Publish the migrations and seeders stubs.
     */
    protected function publishDatabaseResources(): void
    {
        // Migrations
        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'database/migrations', files: [
                '2014_10_12_200000_add_two_factor_columns_to_users_table',
                '2022_01_12_072103_add_avatar_to_users_table',
                '2022_01_12_130803_create_permission_tables',
            ]),
        ], groups: 'generator-migration');

        // Seeders
        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'database/seeders', files: [
                'DatabaseSeeder',
                'RoleAndPermissionSeeder',
                'UserSeeder',
            ]),
        ], groups: 'generator-seeder');
    }

    /**
     * Publish the User model.
     *
     * This method publishes the User model to the specified destination.
     * The model is located in the "app/Models" directory by default.
     */
    protected function publishModels(): void
    {
        $this->publishes(paths: [
            __DIR__ . '/../../stubs/publish/models/User.stub' => app_path(path: 'Models/User.php'),
        ], groups: 'generator-model');
    }

    /**
     * Publish assets to the public directory.
     *
     * This method publishes the asset files necessary for the generator's public resources.
     * These assets include stylesheets, scripts, and other files that are required for the frontend components of the generator to function correctly.
     */
    protected function publishAssets(): void
    {
        $this->publishes(paths: [
            __DIR__ . '/../../assets' => public_path(path: 'mazer'),
        ], groups: 'generator-assets');
    }

    /**
     * Publish utility files.
     *
     * This method publishes a set of utility files which include interfaces, services, and other helpers necessary for image processing and related functionalities.
     * The files are organized into specific groups for targeted publishing.
     */
    protected function publishUtils(): void
    {
        $utilsFiles = [
            'Interfaces/ImageServiceInterfaceV2',
            'Services/ImageServiceV2',
            'helper',
            'ImageUploadOption',
        ];

        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'utils', files: $utilsFiles),
        ], groups: 'generator-utils');

        $this->publishes(paths: [
            ...$this->mapStubsToPaths(category: 'utils', files: $utilsFiles),
        ], groups: 'image-service-v2');
    }

    /**
     * Publish bootstrap files.
     *
     * This method publish the bootstrap files that are used to configure the generator.
     * The full version of bootstrap files is used when the generator is configured to use the full version.
     * The simple version of bootstrap files is used when the generator is configured to use the simple version.
     */
    protected function publishBootstrapFiles(): void
    {
        $this->publishes(paths: [
            __DIR__ . '/../../stubs/publish/bootstrap/full-version/app.stub' => base_path(path: 'bootstrap/app.php'),
        ], groups: 'generator-bootstrap-app-full');

        $this->publishes(paths: [
            __DIR__ . '/../../stubs/publish/bootstrap/simple-version/app.stub' => base_path(path: 'bootstrap/app.php'),
        ], groups: 'generator-bootstrap-app-simple');
    }

    /**
     * Add generator information to the about command.
     *
     * This method is called automatically by the service provider.
     */
    protected function registerAboutInfo(): void
    {
        AboutCommand::add(section: 'Generator', data: fn(): array => [
            'Version' => '0.5.0',
            'Source' => 'https://github.com/evdigiina/generator',
            'Docs' => 'https://zzzul.github.io/generator-docs-next/',
            'About' => 'Automate CRUD, Focus on Core Features.',
        ]);
    }

    /**
     * Register console commands with the application.
     *
     * This method checks if the application is running in the console environment and registers an array of console commands to be accessible via Artisan CLI.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(commands: [
                PublishAllFiles::class,
                SetSidebarType::class,
                PublishApiCommand::class,
                PublishUtilsCommand::class,
                PublishImageServiceV2Command::class,
            ]);
        }
    }

    /**
     * Helper method to map stub files to their destination paths
     */
    protected function mapStubsToPaths(string $category, array $files): array
    {
        $paths = [];

        foreach ($files as $file) {
            $stubPath = __DIR__ . "/../../stubs/publish/{$category}/{$file}.stub";
            $destination = $this->getDestinationPath(category: $category, file: $file);

            $paths[$stubPath] = $destination;
        }

        return $paths;
    }

    /**
     * Determine the destination path based on category and filename
     */
    protected function getDestinationPath(string $category, string $file): string
    {
        return match (true) {
            str_starts_with(haystack: $category, needle: 'views/') => resource_path(
                path: 'views/' . str_replace('views/', '', $category) . "/{$file}.php"
            ),
            str_starts_with(haystack: $category, needle: 'controllers') && !str_starts_with(haystack: $category, needle: 'controllers/api') => app_path(
                path: "Http/Controllers/{$file}.php"
            ),
            str_starts_with(haystack: $category, needle: 'controllers/api') => app_path(
                path: "Http/Controllers/Api/{$file}.php"
            ),
            str_starts_with(haystack: $category, needle: 'requests') => $this->buildNamespacedPath(
                category: $category,
                file: $file,
                base: 'Http/Requests'
            ),
            str_starts_with(haystack: $category, needle: 'resources') => $this->buildNamespacedPath(
                category: $category,
                file: $file,
                base: 'Http/Resources'
            ),
            str_starts_with(haystack: $category, needle: 'fortify') => app_path(
                path: "Actions/Fortify/{$file}.php"
            ),
            str_starts_with(haystack: $category, needle: 'providers') => app_path(
                path: "Providers/{$file}.php"
            ),
            str_starts_with(haystack: $category, needle: 'utils') => $this->buildUtilsPath(file: $file),
            str_starts_with(haystack: $category, needle: 'database/migrations') => database_path(
                path: "migrations/{$file}.php"
            ),
            str_starts_with(haystack: $category, needle: 'database/seeders') => database_path(
                path: "seeders/{$file}.php"
            ),
            str_starts_with(haystack: $category, needle: 'config') => config_path(
                path: "{$file}.php"
            ),
            default => ''
        };
    }

    /**
     * Construct the path to a file within a namespaced directory.
     *
     * This method is used to build the path to a file within a directory that may have a namespace, such as a request or resource.
     * The `category` parameter is expected to contain the namespace as the second element of an exploded string.
     * If the namespace is not present, an empty string is used instead.
     */
    protected function buildNamespacedPath(string $category, string $file, string $base): string
    {
        $parts = explode(separator: '/', string: $category);
        $namespace = $parts[1] ?? '';

        return app_path(path: "{$base}/{$namespace}/{$file}.php");
    }

    /**
     * Build the path to a utility file within the Generators directory.
     *
     * If the file name contains a '/', it assumes a subdirectory structure within the Generators directory and constructs the path accordingly.
     * Otherwise, it * assumes the file is directly within the Generators directory.
     */
    protected function buildUtilsPath(string $file): string
    {
        if (str_contains(haystack: $file, needle: '/')) {
            $parts = explode(separator: '/', string: $file);

            return app_path(path: "Generators/{$parts[0]}/{$parts[1]}.php");
        }

        return app_path(path: "Generators/{$file}.php");
    }
}
