<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;

class HelperTest extends TestCase
{
    // #[Test]
    // public function it_function_is_active_menu_is_exist()
    // {
    //     $this->assertTrue(function_exists(is_active_menu('/generators/create')));
    // }

    #[Test]
    public function it_helper_file_exists(): void
    {

        $this->assertTrue(file_exists(__DIR__ . '/../src/helper.php'));
    }
}
