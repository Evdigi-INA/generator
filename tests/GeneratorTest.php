<?php

namespace Tests;

use EvdigiIna\Generator\Enums\GeneratorType;
use EvdigiIna\Generator\Enums\GeneratorVariant;
use EvdigiIna\Generator\Generators\GeneratorUtils;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use PHPUnit\Framework\Attributes\Test;
use Random\RandomException;

class GeneratorTest extends TestCase
{
    use InteractsWithViews;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterApplicationCreated(function () {
            file_put_contents(storage_path('generator.cache'), '{"simple_version_publish_count":0,"full_version_publish_count":2}');
        });

        $this->beforeApplicationDestroyed(function () {
            file_put_contents(storage_path('generator.cache'), '{"simple_version_publish_count":0,"full_version_publish_count":2}');
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

    /**
     * @throws RandomException
     */
    #[Test]
    public function it_can_create_new_module(): void
    {
        $this->withoutExceptionHandling();

        $modelName = 'Generator'.$this->generateRandomString();

        $this->postJson('/simple-generators', json_decode('{"requireds":["yes","yes","yes","yes","yes","yes","yes","no","no","no","yes","yes","no","no","yes","yes","yes","yes","yes","yes","yes","yes","yes","yes"],"_token":"LIV8Mj4vCRSIGWhbQFVcZax6jwnsZMS7JAWGhdTe","_method":"POST","model":"'.$modelName.'","generate_type":"all","generate_variant":"default","generate_seeder":"on","generate_factory":"on","fields":["q","w","e","r","t","y","u","i","o","p","a","s","d","f","g","h","j","k","l","z","x","c","v","b"],"column_types":["string","string","string","string","string","string","string","string","string","string","integer","integer","integer","integer","boolean","boolean","boolean","date","date","time","year","year","dateTime","enum"],"select_options":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,"PHP|Laravel"],"constrains":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"foreign_ids":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"on_update_foreign":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"on_delete_foreign":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"min_lengths":[null,null,null,null,null,null,null,null,null,null,null,"1",null,null,null,null,null,null,null,null,null,null,null,null],"max_lengths":[null,null,null,null,null,null,null,null,null,null,null,"100",null,null,null,null,null,null,null,null,null,null,null,null],"input_types":["text","textarea","email","tel","password","url","search","file","hidden","no-input","number","range","hidden","no-input","select","radio","datalist","date","month","time","select","datalist","datetime-local","select"],"file_types":[null,null,null,null,null,null,null,"image",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"files_sizes":[null,null,null,null,null,null,null,"1024",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"mimes":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null],"steps":[null,null,null,null,null,null,null,null,null,null,null,"1",null,null,null,null,null,null,null,null,null,null,null,null],"default_values":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null]}', true))->assertSuccessful();

        $this->withOutExceptionHandling();
    }

    /**
     * @throws RandomException
     */
    public function generateRandomString($length = 5): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    #[Test]
    public function it_validates_required_fields_on_store(): void
    {
        $this->postJson('/simple-generators', [])
            ->assertStatus(422);
    }

    #[Test]
    public function it_accepts_target_field(): void
    {
        $modelName = 'TargetTest'.$this->generateRandomString();

        $response = $this->postJson('/simple-generators', [
            'model' => $modelName,
            'generate_type' => GeneratorType::ALL->value,
            'generate_variant' => GeneratorVariant::DEFAULT->value,
            'target' => 'blade',
            'fields' => ['name'],
            'column_types' => ['string'],
            'input_types' => ['text'],
            'requireds' => ['yes'],
            'select_options' => [null],
            'constrains' => [null],
            'foreign_ids' => [null],
            'on_update_foreign' => [null],
            'on_delete_foreign' => [null],
            'min_lengths' => [null],
            'max_lengths' => [null],
            'mimes' => [null],
            'files_sizes' => [null],
            'file_types' => [null],
            'steps' => [null],
            'default_values' => [null],
        ]);

        $response->assertCreated();
    }

    #[Test]
    public function it_can_generate_module_with_soft_deletes(): void
    {
        $modelName = 'SoftDeleteTest'.$this->generateRandomString();

        $response = $this->postJson('/simple-generators', [
            'model' => $modelName,
            'generate_type' => GeneratorType::ALL->value,
            'generate_variant' => GeneratorVariant::DEFAULT->value,
            'soft_delete' => '1',
            'fields' => ['name'],
            'column_types' => ['string'],
            'input_types' => ['text'],
            'requireds' => ['yes'],
            'select_options' => [null],
            'constrains' => [null],
            'foreign_ids' => [null],
            'on_update_foreign' => [null],
            'on_delete_foreign' => [null],
            'min_lengths' => [null],
            'max_lengths' => [null],
            'mimes' => [null],
            'files_sizes' => [null],
            'file_types' => [null],
            'steps' => [null],
            'default_values' => [null],
        ]);

        $response->assertCreated();

        $modelPath = app_path("Models/$modelName.php");
        $this->assertFileExists($modelPath);
        $modelContent = file_get_contents($modelPath);
        $this->assertStringContainsString('use Illuminate\Database\Eloquent\SoftDeletes;', $modelContent);
        $this->assertStringContainsString('use SoftDeletes;', $modelContent);
    }

    #[Test]
    public function it_can_generate_module_with_unique_validation(): void
    {
        $modelName = 'UniqueTest'.$this->generateRandomString();

        $response = $this->postJson('/simple-generators', [
            'model' => $modelName,
            'generate_type' => GeneratorType::ALL->value,
            'generate_variant' => GeneratorVariant::DEFAULT->value,
            'fields' => ['email', 'name'],
            'column_types' => ['string', 'string'],
            'input_types' => ['email', 'text'],
            'requireds' => ['yes', 'yes'],
            'uniques' => ['1', null],
            'select_options' => [null, null],
            'constrains' => [null, null],
            'foreign_ids' => [null, null],
            'on_update_foreign' => [null, null],
            'on_delete_foreign' => [null, null],
            'min_lengths' => [null, null],
            'max_lengths' => [null, null],
            'mimes' => [null, null],
            'files_sizes' => [null, null],
            'file_types' => [null, null],
            'steps' => [null, null],
            'default_values' => [null, null],
        ]);

        $response->assertCreated();

        $modelPath = app_path("Models/$modelName.php");
        $this->assertFileExists($modelPath);
        $modelContent = file_get_contents($modelPath);
        $this->assertStringContainsString("class $modelName extends Model", $modelContent);

        $pluralPascal = GeneratorUtils::pluralPascalCase($modelName);
        $requestPath = app_path("Http/Requests/{$pluralPascal}/Store{$modelName}Request.php");
        $this->assertFileExists($requestPath);
        $requestContent = file_get_contents($requestPath);
        $this->assertStringContainsString("'unique:", $requestContent);
        $this->assertStringContainsString(",email'", $requestContent);
    }

    #[Test]
    public function it_can_generate_module_with_tests(): void
    {
        $modelName = 'TestGen'.$this->generateRandomString();

        $response = $this->postJson('/simple-generators', [
            'model' => $modelName,
            'generate_type' => GeneratorType::ALL->value,
            'generate_variant' => GeneratorVariant::DEFAULT->value,
            'generate_tests' => '1',
            'fields' => ['name'],
            'column_types' => ['string'],
            'input_types' => ['text'],
            'requireds' => ['yes'],
            'select_options' => [null],
            'constrains' => [null],
            'foreign_ids' => [null],
            'on_update_foreign' => [null],
            'on_delete_foreign' => [null],
            'min_lengths' => [null],
            'max_lengths' => [null],
            'mimes' => [null],
            'files_sizes' => [null],
            'file_types' => [null],
            'steps' => [null],
            'default_values' => [null],
        ]);

        $response->assertCreated();

        $pluralPascal = GeneratorUtils::pluralPascalCase($modelName);
        $testPath = base_path("tests/Feature/{$pluralPascal}/{$modelName}Test.php");
        $this->assertFileExists($testPath);
        $testContent = file_get_contents($testPath);
        $this->assertStringContainsString("namespace Tests\Feature\\{$pluralPascal};", $testContent);
    }

    #[Test]
    public function it_can_store_simple_module_with_minimal_fields(): void
    {
        $modelName = 'SimpleTest'.$this->generateRandomString();

        $response = $this->postJson('/simple-generators', [
            'model' => $modelName,
            'generate_type' => GeneratorType::ALL->value,
            'generate_variant' => GeneratorVariant::DEFAULT->value,
            'fields' => ['name', 'description'],
            'column_types' => ['string', 'text'],
            'input_types' => ['text', 'textarea'],
            'requireds' => ['yes', 'no'],
            'select_options' => [null, null],
            'constrains' => [null, null],
            'foreign_ids' => [null, null],
            'on_update_foreign' => [null, null],
            'on_delete_foreign' => [null, null],
            'min_lengths' => [null, null],
            'max_lengths' => [null, null],
            'mimes' => [null, null],
            'files_sizes' => [null, null],
            'file_types' => [null, null],
            'steps' => [null, null],
            'default_values' => [null, null],
        ]);

        $response->assertCreated()
            ->assertJson(['message' => 'success']);
    }

    #[Test]
    public function it_detects_file_conflict_on_duplicate_generation(): void
    {
        $modelName = 'ConflictTest'.$this->generateRandomString();

        $this->postJson('/simple-generators', [
            'model' => $modelName,
            'generate_type' => GeneratorType::ALL->value,
            'generate_variant' => GeneratorVariant::DEFAULT->value,
            'fields' => ['name'],
            'column_types' => ['string'],
            'input_types' => ['text'],
            'requireds' => ['yes'],
            'select_options' => [null],
            'constrains' => [null],
            'foreign_ids' => [null],
            'on_update_foreign' => [null],
            'on_delete_foreign' => [null],
            'min_lengths' => [null],
            'max_lengths' => [null],
            'mimes' => [null],
            'files_sizes' => [null],
            'file_types' => [null],
            'steps' => [null],
            'default_values' => [null],
        ])->assertCreated();

        $this->postJson('/simple-generators', [
            'model' => $modelName,
            'generate_type' => GeneratorType::ALL->value,
            'generate_variant' => GeneratorVariant::DEFAULT->value,
            'fields' => ['name'],
            'column_types' => ['string'],
            'input_types' => ['text'],
            'requireds' => ['yes'],
            'select_options' => [null],
            'constrains' => [null],
            'foreign_ids' => [null],
            'on_update_foreign' => [null],
            'on_delete_foreign' => [null],
            'min_lengths' => [null],
            'max_lengths' => [null],
            'mimes' => [null],
            'files_sizes' => [null],
            'file_types' => [null],
            'steps' => [null],
            'default_values' => [null],
        ])->assertStatus(403);
    }
}
