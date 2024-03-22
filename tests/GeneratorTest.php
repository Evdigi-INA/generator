<?php

namespace Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;

class GeneratorTest extends TestCase
{
    use InteractsWithViews;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterApplicationCreated(function () {
            file_put_contents(__DIR__ . '/../generator-cache', '{"simple_version_publish_count":0,"full_version_publish_count":1}');

            Artisan::call('migrate:fresh --seed');
        });

        $this->beforeApplicationDestroyed(function () {
            file_put_contents(__DIR__ . '/../generator-cache', '{"simple_version_publish_count":0,"full_version_publish_count":1}');

            Artisan::call('migrate:fresh --seed');
        });
    }

    #[Test]
    public function it_can_render_generator_create_page(): void
    {
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

    // #[Test]
    public function it_can_create_new_module_using_a_simple_generator_version(): void
    {
        $this->withoutExceptionHandling();

        $modelName = 'Generator' . $this->generateRandomString();

        $this->post('/simple-generators', json_decode('{
            "requireds": [
                "yes",
                "no"
            ],
            "_token": "mFXgQ36wdxA3tbL0zz74ikc9cUe3z2IWe4YN30zv",
            "_method": "POST",
            "model":  "'. $modelName . '",
            "generate_type": "all",
            "generate_variant": "default",
            "generate_seeder": "on",
            "generate_factory": "on",
            "fields": [
                "name",
                "logo"
            ],
            "column_types": [
                "string",
                "string"
            ],
            "select_options": [
                null,
                null
            ],
            "constrains": [
                null,
                null
            ],
            "foreign_ids": [
                null,
                null
            ],
            "on_update_foreign": [
                null,
                null
            ],
            "on_delete_foreign": [
                null,
                null
            ],
            "min_lengths": [
                null,
                null
            ],
            "max_lengths": [
                null,
                null
            ],
            "input_types": [
                "text",
                "file"
            ],
            "file_types": [
                null,
                "image"
            ],
            "files_sizes": [
                null,
                "1024"
            ],
            "mimes": [
                null,
                null
            ],
            "steps": [
                null,
                null
            ],
            "default_values": [
                null,
                null
            ]
        }', true))->assertSuccessful();
    }

    function generateRandomString($length = 5): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) $randomString .= $characters[random_int(0, $charactersLength - 1)];
        return $randomString;
    }
}
