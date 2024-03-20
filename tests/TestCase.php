<?php

namespace Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Attributes\WithMigration;
use PHPUnit\Framework\Attributes\Test;

#[WithMigration]
class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench, InteractsWithViews, RefreshDatabase;

    // /**
    //  * Get package providers.
    //  */
    // protected function getPackageProviders($app)
    // {
    //     return [
    //         'EvdigiIna\GeneratorServiceProvider',
    //     ];
    // }

    // /**
    //  * Define environment setup.
    //  *
    //  * @param  \Illuminate\Foundation\Application  $app
    //  * @return void
    //  */
    // protected function defineEnvironment($app)
    // {
    //     // Setup default database to use sqlite :memory:
    //     tap($app['config'], function (Repository $config) {
    //         $config->set('database.default', 'testbench');
    //         $config->set('database.connections.testbench', [
    //             'driver'   => 'sqlite',
    //             'database' => ':memory:',
    //             'prefix'   => '',
    //         ]);

    //         // Setup queue database connections.
    //         $config([
    //             'queue.batching.database' => 'testbench',
    //             'queue.failed.database' => 'testbench',
    //         ]);
    //     });
    // }

    #[Test]
    public function it_has_generator_utils_test_class()
    {
        $this->assertTrue(class_exists(GeneratorUtilsTest::class));
    }
}
