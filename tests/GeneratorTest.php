<?php

namespace Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use PHPUnit\Framework\Attributes\Test;

class GeneratorTest extends TestCase
{
    use InteractsWithViews;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterApplicationCreated(function () {
            file_put_contents(__DIR__ . '/../generator-cache', '{"simple_version_publish_count":0,"full_version_publish_count":1}');
        });

        $this->beforeApplicationDestroyed(function () {
            file_put_contents(__DIR__ . '/../generator-cache', '{"simple_version_publish_count":0,"full_version_publish_count":1}');
        });
    }

    #[Test]
    public function it_can_render_generator_create_page(): void
    {
        $this->withoutExceptionHandling();

        $this->get('/generators/create')->assertStatus(200)->assertSee('Generators');
    }

    #[Test]
    public function it_can_render_simple_generator_create_page(): void
    {
        $this->get('/simple-generators/create')->assertStatus(200);
    }

    #[Test]
    public function it_can_render_api_generator_create_page(): void
    {
        $this->get('/api-generators/create')->assertStatus(200)->assertSee('API Generators');
    }
}
