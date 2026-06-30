<?php

namespace Tests;

use EvdigiIna\Generator\Providers\GeneratorServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Framework\Attributes\Test;
use Yajra\DataTables\DataTablesServiceProvider;

#[WithMigration]
class TestCase extends \Orchestra\Testbench\TestCase
{
    use InteractsWithViews, RefreshDatabase, WithWorkbench;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            // Artisan::call('view:clear');
        });

        $this->beforeApplicationDestroyed(function () {
            // Artisan::call('view:clear');
        });

        parent::setUp();
    }

    /**
     * Get package providers.
     *
     * @param  Application  $app
     * @return array<int, class-string<ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            GeneratorServiceProvider::class,
            DataTablesServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('view.paths', [
            __DIR__.'/views',
            resource_path('views'),
        ]);

        $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('filesystems.disks.unit-downloads', [
            'driver' => 'local',
            'root' => __DIR__.'/fixtures',
        ]);
    }

    #[Test]
    public function it_has_generator_utils_test_class()
    {
        $this->assertTrue(class_exists(GeneratorUtilsTest::class));
    }

    #[Test]
    public function it_has_generator_command_test_class()
    {
        $this->assertTrue(class_exists(CommandTest::class));
    }

    #[Test]
    public function it_has_generator_test_class()
    {
        $this->assertTrue(class_exists(GeneratorTest::class));
    }

    #[Test]
    public function it_has_generator_helper_test_class()
    {
        $this->assertTrue(class_exists(HelperTest::class));
    }
}
