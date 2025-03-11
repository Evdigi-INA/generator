<?php

namespace Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Framework\Attributes\Test;

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
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            \EvdigiIna\Generator\Providers\GeneratorServiceProvider::class,
            \Yajra\DataTables\DataTablesServiceProvider::class,
        ];
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
