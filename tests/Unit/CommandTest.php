<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommandTest extends TestCase
{
    // #[Test]
    // public function it_can_run_generator_sidebar_command()
    // {
    //     $this->withoutExceptionHandling();

    //     $this->artisan('generator:sidebar static')->assertSuccessful();
    // }

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
}
