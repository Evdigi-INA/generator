<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;

class CommandTest extends TestCase
{
    protected function setUp(): void
    {
        // Clean up stale migration files from prior test runs in the same process.
        // Testbench stores migrations under vendor/orchestra/testbench-core/laravel/database/migrations.
        // Without cleanup, old migration files accumulate across tests and cause
        // "table already exists" errors when a new migration for the same table is created.
        $migrationsDir = __DIR__ . '/../vendor/orchestra/testbench-core/laravel/database/migrations';
        foreach (glob("$migrationsDir/*.php") as $file) {
            unlink($file);
        }

        parent::setUp();
    }

    #[Test]
    public function it_can_run_generator_install_full_command()
    {
        $this->withoutExceptionHandling();

        $this->artisan('generator:install full')->assertSuccessful()->assertExitCode(0);
    }

    #[Test]
    public function it_can_run_generator_install_simple_command()
    {
        $this->withoutExceptionHandling();

        $this->artisan('generator:install simple')->assertSuccessful()->assertExitCode(0);
    }

    #[Test]
    public function it_can_run_make_crud_command(): void
    {
        $this->withoutExceptionHandling();

        $this->artisan('make:crud', [
            'model' => 'CliTest',
            '--variant' => 'default',
            '--target' => 'blade',
            '--no-interaction' => true,
        ])->assertSuccessful()->assertExitCode(0);
    }
}
