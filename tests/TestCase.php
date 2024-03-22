<?php

namespace Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\Attributes\WithMigration;
use PHPUnit\Framework\Attributes\Test;
use function Orchestra\Testbench\workbench_path;

#[WithMigration]
class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench, InteractsWithViews, RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            Artisan::call('view:clear');
            Artisan::call('migrate:fresh --seed');
        });

        $this->beforeApplicationDestroyed(function () {
            Artisan::call('view:clear');
            Artisan::call('migrate:fresh --seed');
        });

        parent::setUp();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            \EvdigiIna\Generator\Providers\GeneratorServiceProvider::class,
        ];
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(workbench_path('database/migrations'));
    }

    /**
     * Define routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        // $router->get('/generators/create', [GeneratorController::class, 'create']);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('view.paths', [
            __DIR__ . '/views',
            resource_path('views'),
        ]);

        $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('filesystems.disks.unit-downloads', [
            'driver' => 'local',
            'root' => __DIR__ . '/fixtures',
        ]);
    }

    #[Test]
    public function it_has_generator_utils_test_class()
    {
        $this->withoutExceptionHandling();

        $this->assertTrue(class_exists(GeneratorUtilsTest::class));
    }
}
