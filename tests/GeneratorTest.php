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
            file_put_contents(__DIR__ . '/../generator.cache', '{"simple_version_publish_count":0,"full_version_publish_count":2}');
        });

        $this->beforeApplicationDestroyed(function () {
            file_put_contents(__DIR__ . '/../generator.cache', '{"simple_version_publish_count":0,"full_version_publish_count":2}');
        });
    }

    #[Test]
    public function it_can_render_generator_create_page(): void
    {
        $this->withoutExceptionHandling();
        $this->get('/generators/create?for_test=1')->assertStatus(200)->assertSee('Generators');
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

    #[Test]
    public function it_can_create_new_module(): void
    {
        $this->withoutExceptionHandling();

        $modelName = 'Generator' . $this->generateRandomString();

        $this->post('/simple-generators', json_decode('{"requireds":["yes","yes","yes","yes","yes","yes","yes","no","no","no","yes","yes","no","no","yes","yes","yes","yes","yes","yes","yes","yes","yes","yes"],"_token":"LIV8Mj4vCRSIGWhbQFVcZax6jwnsZMS7JAWGhdTe","_method":"POST","model":"'. $modelName .'","generate_type":"all","generate_variant":"default","generate_seeder":"on","generate_factory":"on","fields":["q","w","e","r","t","y","u","i","o","p","a","s","d","f","g","h","j","k","l","z","x","c","v","b"],"column_types":["string","string","string","string","string","string","string","string","string","string","integer","integer","integer","integer","boolean","boolean","boolean","date","date","time","year","year","dateTime","enum"],"select_options":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,"PHP|Laravel"],"constrains":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"foreign_ids":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"on_update_foreign":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"on_delete_foreign":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"min_lengths":[null,null,null,null,null,null,null,null,null,null,null,"1",null,null,null,null,null,null,null,null,null,null,null,null],"max_lengths":[null,null,null,null,null,null,null,null,null,null,null,"100",null,null,null,null,null,null,null,null,null,null,null,null],"input_types":["text","textarea","email","tel","password","url","search","file","hidden","no-input","number","range","hidden","no-input","select","radio","datalist","date","month","time","select","datalist","datetime-local","select"],"file_types":[null,null,null,null,null,null,null,"image",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"files_sizes":[null,null,null,null,null,null,null,"1024",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"mimes":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"steps":[null,null,null,null,null,null,null,null,null,null,null,"1",null,null,null,null,null,null,null,null,null,null,null,null],"default_values":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null]}', true))->assertSuccessful();
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
