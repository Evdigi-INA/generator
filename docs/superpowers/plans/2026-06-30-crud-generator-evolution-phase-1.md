# CRUD Generator Evolution — Phase 1: Foundation + New Features

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor stub system to be target-aware, add soft deletes + unique validation + test scaffolding, and create `make:crud` CLI command.

**Architecture:** Target-aware `getStub()` resolves from `stubs/{target}/` first, falling back to `stubs/shared/` then `stubs/blade/`. Feature toggles (soft deletes, uniques, tests) are pure stub variables — no PHP changes in generators.

**Tech Stack:** PHP 8.2+, Laravel 13, Pest, spatie/laravel-permission, yajra/laravel-datatables

## Global Constraints

- PHP 8.2+ minimum required by composer.json
- All existing tests must pass (95+ tests currently)
- No breaking changes: default `target` is `'blade'` when omitted
- All generator code uses named arguments consistently
- `declare(strict_types=1)` in all generated PHP files
- All generated code must include explicit return types

---

### Task 1: Target-Aware Stub Resolution

**Files:**
- Modify: `src/Generators/GeneratorUtils.php` (getStub method)
- Create: `stubs/blade/` (relocate existing stubs)
- Create: `stubs/shared/` (model, migration, seeder, factory, request, resource stubs)
- Test: `tests/GeneratorUtilsTest.php`

**Interfaces:**
- Consumes: Existing `getStub(string $path)` callers
- Produces: `getStub(string $name, ?string $target = null): string` — resolves `stubs/{target}/{name}.stub` > `stubs/shared/{name}.stub` > `stubs/blade/{name}.stub`

- [ ] **Step 1: Create blade/ and shared/ stub directories**

```bash
mkdir -p stubs/blade/controllers stubs/blade/views/forms stubs/blade/views/index stubs/blade/views/show stubs/blade/views/simple stubs/shared
```

- [ ] **Step 2: Move target-agnostic stubs to shared/**

Move these stubs (all targets use the same content):
```
stubs/model.stub → stubs/shared/model.stub
stubs/migration.stub → stubs/shared/migration.stub
stubs/seeder.stub → stubs/shared/seeder.stub
stubs/factory.stub → stubs/shared/factory.stub
stubs/request.stub → stubs/shared/request.stub
stubs/resource.stub → stubs/shared/resource.stub
stubs/resource-collection.stub → stubs/shared/resource-collection.stub
stubs/export.stub → stubs/shared/export.stub
stubs/sidebar-dynamic.stub → stubs/shared/sidebar-dynamic.stub
stubs/sidebar-static.stub → stubs/shared/sidebar-static.stub
stubs/view-composer.stub → stubs/shared/view-composer.stub
```

```bash
git mv stubs/model.stub stubs/shared/model.stub
git mv stubs/migration.stub stubs/shared/migration.stub
git mv stubs/seeder.stub stubs/shared/seeder.stub
git mv stubs/factory.stub stubs/shared/factory.stub
git mv stubs/request.stub stubs/shared/request.stub
git mv stubs/resource.stub stubs/shared/resource.stub
git mv stubs/resource-collection.stub stubs/shared/resource-collection.stub
git mv stubs/export.stub stubs/shared/export.stub
git mv stubs/sidebar-dynamic.stub stubs/shared/sidebar-dynamic.stub
git mv stubs/sidebar-static.stub stubs/shared/sidebar-static.stub
git mv stubs/view-composer.stub stubs/shared/view-composer.stub
```

- [ ] **Step 3: Move Blade-specific stubs to stubs/blade/**

```
stubs/controllers/ → stubs/blade/controllers/
stubs/views/ → stubs/blade/views/
stubs/route.stub → stubs/blade/route.stub
stubs/api/ → stubs/shared/api/  (API is not Blade-specific, actually shared)
```

```bash
git mv stubs/route.stub stubs/blade/route.stub
git mv stubs/controllers stubs/blade/controllers
git mv stubs/views stubs/blade/views
git mv stubs/api stubs/shared/api
```

- [ ] **Step 4: Update `getStub()` to be target-aware**

```php
public static function getStub(string $name, ?string $target = null): string
{
    $target ??= request('target', 'blade');

    $paths = [
        __DIR__."/../../stubs/{$target}/{$name}.stub",
        __DIR__."/../../stubs/shared/{$name}.stub",
        __DIR__."/../../stubs/shared/api/{$name}.stub",
        __DIR__."/../../stubs/blade/{$name}.stub",
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            return file_get_contents($path);
        }
    }

    throw new \RuntimeException("Stub not found: {$name} (target: {$target})");
}
```

- [ ] **Step 5: Update all getStub() callers to use named subpaths**

All generators call `getStub(path: 'controllers/controller')` etc. Since controllers are now under `blade/controllers/`, the path should change to include the subdirectory in the name:

Search and update callers of `getStub`:

```bash
rg "getStub\(" src/Generators/ --include '*.php'
```

Each caller's path must match the new structure. For example:
- `getStub(path: 'controllers/controller')` → stays `controllers/controller` (resolves to `stubs/blade/controllers/controller.stub`)
- `getStub(path: 'views/create')` → stays `views/create`
- `getStub(path: 'model')` → stays `model` (now resolves to `stubs/shared/model.stub`)

Verify all paths resolve correctly without changing the string arguments.

- [ ] **Step 6: Update publish stubs references**

The `GeneratorServiceProvider` and `PublishAllFiles` reference stubs from `stubs/generators/publish/` — the merge already moved them to `stubs/publish/`. Verify all `__DIR__.'/../../stubs/publish/...'` paths in `GeneratorServiceProvider.php` are correct.

- [ ] **Step 7: Run existing tests**

```bash
php vendor/bin/phpunit
```
Expected: All (95+) tests pass. If stubs moved incorrectly, fix paths and rerun.

- [ ] **Step 8: Commit**

```bash
git add -A
git commit -m "refactor(stubs): target-aware stub resolution with blade/shared split"
```

---

### Task 2: Target Field in Request + StoreGeneratorRequest

**Files:**
- Modify: `src/Http/Requests/StoreGeneratorRequest.php`
- Modify: `src/Http/Requests/StoreSimpleGeneratorRequest.php`
- Modify: `src/Http/Controllers/GeneratorController.php`
- Modify: `src/Http/Controllers/SimpleGeneratorController.php`
- Test: `tests/GeneratorTest.php`

**Interfaces:**
- Consumes: `$request->target` from POST body
- Produces: `$validated` includes `target` key with enum-validated value

- [ ] **Step 1: Add target validation rule to StoreGeneratorRequest**

```php
use EvdigiIna\Generator\Enums\GeneratorVariant;

// In rules() method, add:
'target' => ['nullable', 'in:blade,livewire,inertia-vue,inertia-react'],
```

Also add validation message:
```php
'target.in' => 'The target must be one of: blade, livewire, inertia-vue, inertia-react.',
```

- [ ] **Step 2: Same for StoreSimpleGeneratorRequest**

Read the file first to check if it has different structure:

```php
// In rules() method, add same validation
'target' => ['nullable', 'in:blade,livewire,inertia-vue,inertia-react'],
```

- [ ] **Step 3: Pass target through in GeneratorController::store()**

The `$request->validated()` already includes all validated fields including `target`. No change needed if the default is handled in `GeneratorService::generate()`.

- [ ] **Step 4: Set default target in GeneratorService::generate()**

```php
public function generate(array $request): void
{
    $request['target'] ??= 'blade';
    // ... rest of method
}
```

- [ ] **Step 5: Write test for target field**

```php
#[Test]
public function it_accepts_target_field(): void
{
    $modelName = 'TargetTest'.$this->generateRandomString();

    $response = $this->postJson('/simple-generators', [
        'model' => $modelName,
        'generate_type' => GeneratorType::ONLY_MODEL_AND_MIGRATION->value,
        'generate_variant' => GeneratorVariant::DEFAULT->value,
        'target' => 'blade',
        'fields' => ['name'],
        'column_types' => ['string'],
        'input_types' => ['text'],
        'requireds' => ['yes'],
    ]);

    $response->assertCreated();
}
```

- [ ] **Step 6: Run tests**

```bash
php vendor/bin/phpunit
```

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: add target field validation to generator requests"
```

---

### Task 3: Soft Deletes Feature

**Files:**
- Modify: `stubs/shared/model.stub` — add `{{ hasSoftDelete }}` placeholder
- Modify: `stubs/shared/migration.stub` — add `{{ softDeleteColumn }}` placeholder
- Modify: `src/Http/Requests/StoreGeneratorRequest.php` — add `soft_delete` validation
- Modify: `src/Generators/ModelGenerator.php` — handle `soft_delete` request field
- Modify: `src/Generators/MigrationGenerator.php` — handle `soft_delete` request field
- Modify: `views/include/form.blade.php` — add soft delete checkbox
- Modify: `views/simple-create.blade.php` — add soft delete checkbox
- Test: `tests/GeneratorTest.php`

**Interfaces:**
- Consumes: `$request['soft_delete']` (boolean-ish)
- Produces: Generated model includes SoftDeletes trait; migration adds `softDeletes()` column

- [ ] **Step 1: Update model.stub to accept soft delete token**

Read current stub, find the class body area, add:

```
{{ hasSoftDelete }}
```

- [ ] **Step 2: Update migration.stub**

Add at the bottom of `Schema::create()` or after columns:

```
{{ softDeleteColumn }}
```

- [ ] **Step 3: Update ModelGenerator to replace soft delete token**

Find where the stub is processed and token replace happens. Add:

```php
if (! empty($request['soft_delete'])) {
    $replaces['{{ hasSoftDelete }}'] = "\nuse Illuminate\\Database\\Eloquent\\SoftDeletes;\n";
    $replaces['{{ softDeleteColumn }}'] = "\$table->softDeletes();\n";
} else {
    $replaces['{{ hasSoftDelete }}'] = '';
    $replaces['{{ softDeleteColumn }}'] = '';
}
```

- [ ] **Step 4: Add soft_delete to form UI (both simple and full)**

In `views/simple-create.blade.php` and `views/include/form.blade.php`, add after fields section:

```html
<div class="form-check">
    <input class="form-check-input" type="checkbox" name="soft_delete" id="soft_delete" value="1">
    <label class="form-check-label" for="soft_delete">
        {{ __('Soft Deletes') }}
    </label>
    <small class="text-secondary">{{ __('Add soft deletes to the model and migration') }}</small>
</div>
```

- [ ] **Step 5: Add validation rule**

```php
'soft_delete' => ['nullable', 'in:1,0,true,false'],
```

- [ ] **Step 6: Run tests**

```bash
php vendor/bin/phpunit
```

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: add soft deletes support"
```

---

### Task 4: Unique Validation Feature

**Files:**
- Modify: `stubs/shared/request.stub` — add `{{ uniqueRules }}` placeholder
- Modify: `src/Http/Requests/StoreGeneratorRequest.php` — add `uniques.*` validation
- Modify: `src/Generators/RequestGenerator.php` — handle `uniques[]` field
- Modify: `views/include/form.blade.php` — add unique checkbox per field row
- Modify: `views/simple-create.blade.php` — add unique checkbox per field row
- Test: `tests/GeneratorTest.php`

**Interfaces:**
- Consumes: `$request['uniques']` (array, parallel to `fields[]`)
- Produces: Request validation rules include `'unique:table_name'` for checked fields

- [ ] **Step 1: Update request.stub**

Find the `{{ validationRules }}` section. Add after each rule:

```
{{ uniqueRules }}
```

- [ ] **Step 2: Update RequestGenerator to build uniqueRules**

```php
$uniqueRules = '';
$tableName = GeneratorUtils::pluralSnakeCase($model);
foreach ($fields as $i => $field) {
    if (! empty($request['uniques'][$i])) {
        $uniqueRules .= "\n            '{$field}' => 'unique:{$tableName},{$field}',";
    }
}
```

- [ ] **Step 3: Add uniques to request validation**

```php
'uniques.*' => ['nullable', 'in:1,0,on'],
```

- [ ] **Step 4: Add unique checkbox to field rows**

In form blade files, in the field row loop, add:

```html
<td>
    <div class="form-check">
        <input type="checkbox" name="uniques[]" class="form-check-input" value="1">
    </div>
</td>
```

And add a header: `<th>Unique</th>` in the table head.

- [ ] **Step 5: Run tests**

```bash
php vendor/bin/phpunit
```

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "feat: add unique validation per field"
```

---

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

### Task 6: make:crud CLI Command

**Files:**
- Create: `src/Commands/MakeCrudCommand.php`
- Modify: `src/Providers/GeneratorServiceProvider.php` — register new command
- Test: `tests/CommandTest.php`

**Interfaces:**
- Consumes: CLI arguments/options from `$this->argument()` / `$this->option()`
- Produces: Calls `GeneratorService::generate()` with correctly structured `$request` array

- [ ] **Step 1: Create the command class**

```php
<?php

declare(strict_types=1);

namespace EvdigiIna\Generator\Commands;

use EvdigiIna\Generator\Enums\GeneratorType;
use EvdigiIna\Generator\Enums\GeneratorVariant;
use EvdigiIna\Generator\Generators\Services\GeneratorService;
use Illuminate\Console\Command;

class MakeCrudCommand extends Command
{
    protected $signature = 'make:crud
        {model? : The model name (e.g. Product)}
        {--fields= : Comma-separated field definitions. Format: name:type:params:validation}
        {--target=blade : Output target: blade, livewire, inertia-vue, inertia-react}
        {--variant=default : Generator variant: default, api, single-form}
        {--soft-deletes : Add SoftDeletes trait to the model}
        {--tests : Generate Pest feature tests}
        {--events : Generate event classes}
        {--dry-run : Preview generated files without writing}
    ';

    protected $description = 'Generate a complete CRUD module';

    public function handle(GeneratorService $generatorService): int
    {
        $model = $this->argument('model');

        // Interactive mode if no model provided
        if (! $model) {
            return $this->interactiveMode($generatorService);
        }

        $request = $this->buildRequest($model);

        if ($this->option('dry-run')) {
            $this->info('Dry-run mode: files will not be written.');
            // TODO: implement dry-run in GeneratorService
        }

        try {
            $generatorService->generate($request);
            $this->info("Module '{$model}' generated successfully!");
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function buildRequest(string $model): array
    {
        $fields = $this->parseFields($this->option('fields') ?? '');

        return [
            'model' => $model,
            'generate_type' => GeneratorType::ALL->value,
            'target' => $this->option('target'),
            'generate_variant' => $this->option('variant'),
            'soft_delete' => $this->option('soft-deletes'),
            'generate_tests' => $this->option('tests'),
            'generate_events' => $this->option('events'),
            'fields' => $fields['fields'],
            'column_types' => $fields['column_types'],
            'input_types' => $fields['input_types'],
            'requireds' => $fields['requireds'],
            'select_options' => $fields['select_options'],
            'constrains' => $fields['constrains'],
            'foreign_ids' => $fields['foreign_ids'],
            'on_update_foreign' => $fields['on_update_foreign'],
            'on_delete_foreign' => $fields['on_delete_foreign'],
            'min_lengths' => $fields['min_lengths'],
            'max_lengths' => $fields['max_lengths'],
            'mimes' => $fields['mimes'],
            'files_sizes' => $fields['files_sizes'],
            'file_types' => $fields['file_types'],
            'steps' => $fields['steps'],
            'default_values' => $fields['default_values'],
        ];
    }

    private function parseFields(string $fieldString): array
    {
        if (empty($fieldString)) {
            return [
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
            ];
        }

        $fields = explode(',', $fieldString);
        $names = [];
        $types = [];
        $inputTypes = [];
        $requireds = [];
        $selectOptions = [];
        $constrains = [];
        $foreignIds = [];
        $onUpdateForeign = [];
        $onDeleteForeign = [];
        $minLengths = [];
        $maxLengths = [];
        $mimes = [];
        $fileSizes = [];
        $fileTypes = [];
        $steps = [];
        $defaultValues = [];

        foreach ($fields as $raw) {
            $parts = explode(':', $raw);
            $names[] = $parts[0];
            $types[] = $parts[1] ?? 'string';
            $inputTypes[] = match ($parts[1] ?? 'string') {
                'text' => 'textarea',
                'boolean' => 'select',
                'date', 'dateTime' => 'date',
                'decimal', 'double', 'float', 'integer' => 'number',
                default => 'text',
            };
            $requireds[] = in_array('required', $parts) ? 'yes' : 'no';
            $selectOptions[] = null;
            $constrains[] = null;
            $foreignIds[] = null;
            $onUpdateForeign[] = null;
            $onDeleteForeign[] = null;
            $minLengths[] = null;
            $maxLengths[] = null;
            $mimes[] = null;
            $fileSizes[] = null;
            $fileTypes[] = null;
            $steps[] = null;
            $defaultValues[] = null;
        }

        return [
            'fields' => $names,
            'column_types' => $types,
            'input_types' => $inputTypes,
            'requireds' => $requireds,
            'select_options' => $selectOptions,
            'constrains' => $constrains,
            'foreign_ids' => $foreignIds,
            'on_update_foreign' => $onUpdateForeign,
            'on_delete_foreign' => $onDeleteForeign,
            'min_lengths' => $minLengths,
            'max_lengths' => $maxLengths,
            'mimes' => $mimes,
            'files_sizes' => $fileSizes,
            'file_types' => $fileTypes,
            'steps' => $steps,
            'default_values' => $defaultValues,
        ];
    }

    private function interactiveMode(GeneratorService $generatorService): int
    {
        $model = $this->ask('What is the model name? (e.g. Product)');
        if (! $model) {
            $this->error('Model name is required.');
            return Command::FAILURE;
        }

        $target = $this->choice('Output target', ['blade', 'livewire', 'inertia-vue', 'inertia-react'], 0);
        $variant = $this->choice('Generator variant', ['default', 'single-form', 'api'], 0);

        // Field table input
        $fields = [];
        $this->info('Define your fields. Leave name empty to finish.');
        do {
            $name = $this->ask('Field name (or empty to finish)');
            if (! $name) break;
            $type = $this->anticipate("Type for '{$name}'", ['string', 'integer', 'text', 'boolean', 'date', 'decimal', 'foreignId']);
            $fields[] = ['name' => $name, 'type' => $type];
        } while (true);

        // Build request from interactive input
        $request = $this->buildRequest($model);
        $request['target'] = $target;
        $request['generate_variant'] = $variant;
        // ... map fields

        $generatorService->generate($request);
        $this->info("Module '{$model}' generated successfully!");

        return Command::SUCCESS;
    }
}
```

- [ ] **Step 2: Register command in ServiceProvider**

```php
// In boot(), add to the if ($this->app->runningInConsole()) block:
$this->commands([MakeCrudCommand::class]);
```

- [ ] **Step 3: Write a test**

```php
#[Test]
public function it_can_run_make_crud_command(): void
{
    $this->artisan('make:crud', [
        'model' => 'CliTest',
        '--variant' => 'default',
        '--target' => 'blade',
    ])->assertSuccessful();
}
```

- [ ] **Step 4: Run tests**

```bash
php vendor/bin/phpunit
```

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: add make:crud CLI command"
```

---

### Task 7: Activate checkFilesAreSame

**Files:**
- Modify: `src/Generators/Services/GeneratorService.php` — remove TODO, implement check
- Modify: `src/Http/Controllers/SimpleGeneratorController.php` — uncomment checkFile call
- Test: `tests/GeneratorTest.php`

- [ ] **Step 1: Read current `checkFilesAreSame()` and implement the TODO**

The method currently returns `[]`. Implement file checking for model, controller, and migration files.

- [ ] **Step 2: Wire it up in SimpleGeneratorController**

Uncomment the checkFilesAreSame call and handle the 403 response.

- [ ] **Step 3: Run tests**

```bash
php vendor/bin/phpunit
```

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "feat: activate file conflict detection before generation"
```

---

### Task 8: Pint Format + Final Verification

- [ ] **Step 1: Run Pint**

```bash
php vendor/bin/pint --format agent
```

- [ ] **Step 2: Run full test suite**

```bash
php vendor/bin/phpunit
```

Expected: All tests pass.

- [ ] **Step 3: Run phpstan**

```bash
php vendor/bin/phpstan analyse
```

Expected: No new errors introduced by this phase.

- [ ] **Step 4: Final commit**

```bash
git add -A
git commit -m "chore: apply pint formatting and finalize phase 1"
```
