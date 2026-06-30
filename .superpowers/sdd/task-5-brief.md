### Task 5: Test Scaffolding (Pest)

**Files:**
- Create: `stubs/shared/test.stub` — Pest feature test skeleton
- Create: `src/Generators/TestGenerator.php` — new generator
- Modify: `src/Generators/Services/GeneratorService.php` — call TestGenerator
- Modify: `src/Http/Requests/StoreGeneratorRequest.php` — add `generate_tests` validation
- Modify: `views/include/form.blade.php` — add checkbox
- Modify: `views/simple-create.blade.php` — add checkbox
- Test: `tests/GeneratorTest.php`

**Interfaces:**
- Consumes: `$request['generate_tests']` (boolean)
- Produces: `tests/Feature/{Model}Test.php` with Pest tests

- [ ] **Step 1: Create test.stub**

```blade
<?php

declare(strict_types=1);

namespace Tests\Feature\{{ modelPluralPascal }};

use App\Models\{{ modelName }};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

it('can list {{ modelPluralKebab }}', function () {
    {{ modelSingularCamel }} = {{ modelName }}::factory()->create();

    $response = $this->get('/{{ modelPluralKebab }}');

    $response->assertOk();
});

it('can create a {{ modelSingularKebab }}', function () {
    $response = $this->post('/{{ modelPluralKebab }}', [
        '{{ fields.0.name }}' => 'Test {{ modelName }}',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('{{ modelPluralSnake }}', [
        '{{ fields.0.name }}' => 'Test {{ modelName }}',
    ]);
});

it('validates required fields', function () {
    $response = $this->post('/{{ modelPluralKebab }}', []);

    $response->assertSessionHasErrors(['{{ fields.0.name }}']);
});

it('can show a {{ modelSingularKebab }}', function () {
    ${{ modelSingularCamel }} = {{ modelName }}::factory()->create();

    $response = $this->get('/{{ modelPluralKebab }}/'.${{ modelSingularCamel }}->id);

    $response->assertOk();
});

it('can update a {{ modelSingularKebab }}', function () {
    ${{ modelSingularCamel }} = {{ modelName }}::factory()->create();

    $response = $this->put('/{{ modelPluralKebab }}/'.${{ modelSingularCamel }}->id, [
        '{{ fields.0.name }}' => 'Updated {{ modelName }}',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('{{ modelPluralSnake }}', [
        'id' => ${{ modelSingularCamel }}->id,
        '{{ fields.0.name }}' => 'Updated {{ modelName }}',
    ]);
});

it('can delete a {{ modelSingularKebab }}', function () {
    ${{ modelSingularCamel }} = {{ modelName }}::factory()->create();

    $response = $this->delete('/{{ modelPluralKebab }}/'.${{ modelSingularCamel }}->id);

    $response->assertOk();
    $this->assertModelMissing(${{ modelSingularCamel }});
});
```

- [ ] **Step 2: Create TestGenerator.php**

```php
<?php

declare(strict_types=1);

namespace EvdigiIna\Generator\Generators;

class TestGenerator
{
    public function generate(array $request): void
    {
        if (empty($request['generate_tests'])) {
            return;
        }

        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $name = GeneratorUtils::singularPascalCase($model);
        $pluralKebab = GeneratorUtils::pluralKebabCase($model);
        $singularCamel = GeneratorUtils::singularCamelCase($model);
        $pluralPascal = GeneratorUtils::pluralPascalCase($model);

        $path = base_path("tests/Feature/{$pluralPascal}");
        GeneratorUtils::checkFolder($path);

        $stub = GeneratorUtils::getStub('test');
        $content = GeneratorUtils::replaceStub(
            replaces: [
                '{{ modelName }}' => $name,
                '{{ modelPluralPascal }}' => $pluralPascal,
                '{{ modelPluralKebab }}' => $pluralKebab,
                '{{ modelSingularCamel }}' => $singularCamel,
                '{{ modelPluralSnake }}' => GeneratorUtils::pluralSnakeCase($model),
                '{{ fields.0.name }}' => $request['fields'][0] ?? 'name',
            ],
            stubName: 'test'
        );

        file_put_contents("{$path}/{$name}Test.php", $content);
    }
}
```

- [ ] **Step 3: Register in GeneratorService**

Add `use EvdigiIna\Generator\Generators\TestGenerator;` at top.

In `generate()` method, add after the `(new ExportExcelGenerator)->generate($request);` line:

```php
(new TestGenerator)->generate($request);
```

- [ ] **Step 4: Add validation + form checkbox**

In request rules:
```php
'generate_tests' => ['nullable', 'in:1,0,on'],
```

In form files, add after soft delete checkbox:
```html
<div class="form-check">
    <input class="form-check-input" type="checkbox" name="generate_tests" id="generate_tests" value="1">
    <label class="form-check-label" for="generate_tests">
        {{ __('Generate Tests') }}
    </label>
    <small class="text-secondary">{{ __('Generate Pest feature tests') }}</small>
</div>
```

- [ ] **Step 5: Run tests**

```bash
php vendor/bin/phpunit
```

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "feat: add test scaffolding generator (Pest)"
```

---

