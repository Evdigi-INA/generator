# CRUD Generator Evolution — Design Spec

**Date:** 2026-06-30
**Status:** Approved
**Approach:** Stub-Driven Engine (Pendekatan 1)

## 1. Target System

### 1.1 Targets

`$request['target']` menentukan output frontend stack:

| Target | Frontend | Component Library | Routing |
|--------|----------|-------------------|---------|
| `blade` | Blade + Mazer Bootstrap | Bootstrap 5 | `Route::resource()` |
| `livewire` | Livewire 4 + Flux UI | Flux UI + Tailwind | `Route::get('/{models}', {Model}Index::class)` |
| `inertia-vue` | Inertia + Vue 3 + Composition API | shadcn-vue + Tailwind | `Route::resource()` + `Inertia::render()` |
| `inertia-react` | Inertia + React 19 | shadcn/ui + Tailwind | `Route::resource()` + `Inertia::render()` |

Default: `'blade'` (backward compatible). Aligned with official starter kits:
- `laravel/livewire-starter-kit`
- `laravel/vue-starter-kit`
- `laravel/react-starter-kit`

### 1.2 Target-Agnostic Generators

These generate identical output regardless of target: Model, Migration, Seeder, Factory, Request, Permission, Menu (sidebar stays Blade), ExportExcel, ResourceApi.

### 1.3 Target-Specific Generators

These differ per target: Controller (all variants), View generators (Create/Edit/Index/Show/Form/Action), Route, ViewComposer (Blade only).

### 1.4 Route Patterns

| Target | Route |
|--------|-------|
| blade | `Route::resource('{models}', {Model}Controller::class)` |
| livewire | `Route::get('/{models}', {Model}Index::class)->name('{models}.index')` etc |
| inertia-vue/react | `Route::resource('{models}', {Model}Controller::class)` + controller returns `Inertia::render()` |

### 1.5 View Form Adjustments

Both `simple-create.blade.php` and `create.blade.php` (`@include('generator::include.form')`) get a new **Target** radio/select group:

```
Target: [Blade (Mazer) | Livewire (Flux UI) | Inertia-Vue (shadcn-vue) | Inertia-React (shadcn/ui)]
```

Payload changes:
- New field: `target` (`'blade' | 'livewire' | 'inertia-vue' | 'inertia-react'`)
- When `target = livewire | inertia-*`: `generate_variant` forced to `'default'`, menu/sidebar fields hidden
- When `target = blade`: 100% backward compat
- JS logic (`create-js.blade.js`, `api-create-js.blade.js`) updated to show/hide fields per target

### 1.6 Backward Compatibility

No existing route, form, or payload changes. All existing requests without `target` key default to `'blade'`.

## 2. Stub Architecture

### 2.1 Directory Structure

```
stubs/generators/
├── blade/                           ← existing stubs, relocated from root
│   ├── controllers/
│   ├── views/                       ← create, edit, index, show, form, action
│   ├── route.stub
│   └── view-composer.stub
├── livewire/
│   ├── components/                  ← Index.stub, Create.stub, Edit.stub, Show.stub
│   └── route.stub
├── inertia-vue/
│   ├── controllers/
│   ├── pages/                       ← Index.vue.stub, Create.vue.stub, etc
│   └── route.stub
├── inertia-react/
│   ├── controllers/
│   ├── pages/                       ← Index.tsx.stub, Create.tsx.stub, etc
│   └── route.stub
├── shared/                          ← target-agnostic stubs
│   ├── model.stub
│   ├── migration.stub
│   ├── seeder.stub
│   ├── factory.stub
│   ├── request.stub
│   ├── resource.stub
│   ├── export.stub
│   ├── sidebar-dynamic.stub
│   ├── sidebar-static.stub
│   └── publish/
├── api/                             ← API variant (generate_variant=api)
│   ├── controllers/
│   └── resource.stub
```

### 2.2 Stub Resolution

`GeneratorUtils::getStub(name, ?target)` resolves in order:
1. `stubs/generators/{target}/{name}.stub`
2. `stubs/generators/shared/{name}.stub`
3. `stubs/generators/blade/{name}.stub` (fallback)

### 2.3 Variable System

Extended token set available in all stubs:

| Token | Source | Example |
|-------|--------|---------|
| `{{ modelName }}` | `setModelName()` | `Product` |
| `{{ modelPluralKebab }}` | `pluralKebabCase()` | `products` |
| `{{ modelSingularCamel }}` | `singularCamelCase()` | `product` |
| `{{ modelPluralPascal }}` | `pluralPascalCase()` | `Products` |
| `{{ modelSingularPascal }}` | `singularPascalCase()` | `Product` |
| `{{ modelSingularSnake }}` | `singularSnakeCase()` | `product` |
| `{{ modelPluralSnake }}` | `pluralSnakeCase()` | `products` |
| `{{ namespace }}` | computed | `App\Models` |
| `{{ useStatements }}` | computed | `use Illuminate\Database\Eloquent\Relations\BelongsTo;` |
| `{{ fillableFields }}` | from fields | `'name', 'description'` |
| `{{ castFields }}` | from column types | `'is_active' => 'boolean'` |
| `{{ validationRules }}` | from fields | `'name' => 'required\|string\|max:255'` |
| `{{ uniqueRules }}` | from uniques[] | `'email' => 'unique:users,email'` |
| `{{ relationships }}` | from foreignIds | `public function category(): BelongsTo { ... }` |
| `{{ hasSoftDelete }}` | from soft_delete | `use SoftDeletes;` + `\Illuminate\Database\Eloquent\SoftDeletes` |
| `{{ events }}` | from generate_events | `protected $dispatchesEvents = [...]` |
| `{{ tableName }}` | from model | `products` |
| `{{ columnLoop }}` | loop block | per-field rows |
| `{{ importTypes }}` | from target | `use Inertia\Response;` |
| `{{ hasUpload }}` | from input_types | conditional upload handling |
| `{{ target }}` | from request | `blade`, `livewire` |
| `{{ generateVariant }}` | from request | `default`, `api`, `single-form` |

### 2.4 Feature Toggles via Stubs

All new features are implemented as stub variables + form checkbox fields (no PHP changes):

| Form Field | Stub Token | Affected Stubs |
|------------|-----------|----------------|
| `soft_delete` | `{{ hasSoftDelete }}` | model, migration |
| `uniques[]` | `{{ uniqueRules }}` | request |
| `generate_events` | `{{ events }}` | model |
| `generate_tests` | (generates file) | test stub |
| `bulk_actions` | `{{ bulkActions }}` | controller, index view |
| `print_view` | (generates file) | print view stub |

## 3. New Features

### 3.1 Soft Deletes

Checkbox `soft_delete` in form:
- Migration: adds `$table->softDeletes();`
- Model: adds `use SoftDeletes;` trait + `/** @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes> */`
- Controller: index/show queried with `withTrashed()` option, restore action

### 3.2 Unique Validation

Per-field checkbox `uniques[]`:
- Request stub adds `'unique:{table},{field}'` rule
- Edit request excludes current record: `'unique:{table},{field},' . $this->route('{model}')`

### 3.3 Relationship Scaffolding (beyond belongsTo)

New input `relations[]` per field:
- UI: dropdown after `foreign_ids[]`: `[BelongsTo, HasMany, HasOne, BelongsToMany]`
- Model: generates relationship method with correct return type
- Controller: `with()` eager loading for index/show
- Index view: show related field value

### 3.4 Event Scaffolding

Checkbox `generate_events`:
- Generates `app/Events/{Model}Created.php` and `{Model}Updated.php`
- Model: adds `$dispatchesEvents` property
- Event classes extend `Illuminate\Foundation\Events\Dispatchable`
- Uses existing stub variable `{{ events }}`

### 3.5 Test Scaffolding

Checkbox `generate_tests`:
- Generates `tests/Feature/{Model}Test.php` using Pest
- Tests: index returns 200, create renders form, store validates and creates, show displays, update validates and updates, delete removes
- Uses `RefreshDatabase` + factories
- Only for blade and inertia targets (livewire test approach differs)

### 3.6 Bulk Actions

Checkbox `bulk_actions` (Blade only):
- Index view: checkbox column + "Delete Selected" button
- Controller: `bulkDelete()` method
- Route: `Route::delete('{models}/bulk-delete', ...)->name('{models}.bulk-delete')`

### 3.7 Print View

Checkbox `print_view` (Blade only):
- Generates `show-print.blade.php` with print-optimized layout
- Route: `Route::get('{models}/{model}/print', ...)->name('{models}.print')`

### 3.8 Migration Rollback

Checkbox `rollback_first`:
- Before `Artisan::call('migrate')`, runs `migrate:rollback` for the specific table
- Detects existing migration by table name pattern

## 4. DX Improvements

### 4.1 CLI Command

```bash
php artisan make:crud {model} [options]
```

Options:
- `--fields=name:string,description:text:nullable` — field definitions
- `--target=blade|livewire|inertia-vue|inertia-react` — output target (default: blade)
- `--variant=default|api|single-form` — generator variant
- `--soft-deletes` — add SoftDeletes trait
- `--tests` — generate Pest feature tests
- `--events` — generate event classes
- `--dry-run` — preview without writing

Field format: `name:type:parameters:validation`
Example: `--fields="title:string:nullable:required,price:decimal:8,2:,description:text::nullable"`

Without arguments: interactive mode with prompts (model name first, then field table).

### 4.2 Dry-Run Mode

When `--dry-run`:
- Runs all generators with `dryRun: true` in request
- Generators collect file contents in memory, never write to disk
- Outputs list of files that would be created with file sizes
- Supports `--diff` flag to show content preview

### 4.3 Progress Bar

CLI output uses Laravel `ProgressBar` for all long-running commands:

**`php artisan generator:install full` / `php artisan generator:install simple`:**
```
Installing full version...
  1/17 [▓░░░░░░░░░░░░░░░░░░░░░░░]   6% Running: vendor:publish --tag=generator-views --force
  2/17 [▓▓░░░░░░░░░░░░░░░░░░░░░░]  12% Running: vendor:publish --tag=generator-full-config --force
  ...
 17/17 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100% Complete!

Full version installed successfully!
```

**`php artisan make:crud Product` (generating module):**
```
Generating Product module...
  Model       [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
  Migration   [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
  Controller  [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
  Views       [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
  Route       [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
  Migrating   [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

Module generated successfully! Route: /products
```

The `generator:install` progress bar shows each `vendor:publish` step with its name. Implemented via `executeWithProgress()` in `PublishAllFiles.php` — already present in current merge.

### 4.4 Overwrite Prevention

Activate existing `checkFilesAreSame()` logic to show warning before overwriting existing files.

## 5. Generated Code Quality

### 5.1 Standards for All Generated Code

- `declare(strict_types=1)` in every PHP file
- Named arguments in all method calls (consistent with current codebase)
- Explicit return types: `: JsonResponse`, `: View`, `: RedirectResponse`, `: void`
- Typed properties with readonly where applicable
- Laravel 13 string/array helper idioms (`str()->snake()` not `Str::snake()`)
- PSR-12 formatting (4 spaces, braces on new line for classes)
- PHPDoc blocks on methods (short: `/** ... */`)

### 5.2 Pest Test Quality

Generated tests:
- `uses(RefreshDatabase::class)` for database isolation
- `it('can list {models}')->get(...)->assertOk()`
- `it('can create a {model}')->post(...)->assertCreated()`
- `it('validates required fields')->post(...)->assertSessionHasErrors(...)`
- Namespaced under `Tests\Feature\{Model}`

## 6. Architecture Refactor

### 6.1 GeneratorUtils::getStub() Changes

```php
public static function getStub(string $name, ?string $target = null): string
{
    $target ??= request('target', 'blade');
    $paths = [
        __DIR__."/../../stubs/generators/{$target}/{$name}.stub",
        __DIR__."/../../stubs/generators/shared/{$name}.stub",
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) return file_get_contents($path);
    }
    return file_get_contents(__DIR__."/../../stubs/generators/blade/{$name}.stub");
}
```

### 6.2 GeneratorService::generate() Changes

```php
public function generate(array $request): void
{
    $target = $request['target'] ?? 'blade';

    // Target-agnostic (always runs)
    (new PermissionGenerator)->generate($request);
    (new ModelGenerator)->generate($request);
    (new MigrationGenerator)->generate($request);
    (new RequestGenerator)->generate($request);
    (new SeederGenerator)->generate($request);
    (new FactoryGenerator)->generate($request);
    (new ResourceApiGenerator)->generate($request);

    // Target-specific
    match ($target) {
        'livewire' => $this->generateLivewire($request),
        'inertia-vue', 'inertia-react' => $this->generateInertia($request),
        default => $this->generateBlade($request),
    };

    // Always runs
    (new RouteGenerator)->generate($request);
    (new ExportExcelGenerator)->generate($request);
    Artisan::call('migrate');
}

private function generateBlade(array $request): void { ... }
private function generateLivewire(array $request): void { ... }
private function generateInertia(array $request): void { ... }
```

### 6.3 File Migration

- `stubs/generators/*.stub` → `stubs/generators/blade/*.stub` (all existing stubs)
- `stubs/generators/controllers/` → `stubs/generators/blade/controllers/`
- `stubs/generators/views/` → `stubs/generators/blade/views/`
- `stubs/generators/api/` → merged into `stubs/generators/shared/`

### 6.4 Dead Code Cleanup

- `checkFilesAreSame()` — activated (remove TODO, implement warning)
- `isGenerateApi()` — simplified to check target/variant consistently
- Remove `stubs/generators/api/` (API is a variant, not a target)

## 7. Implementation Order

| Phase | Scope | Depends On |
|-------|-------|------------|
| 1 | Stub relocation + `getStub()` target-aware refactor | — |
| 2 | Blade target stabilizes (existing behavior verified) | Phase 1 |
| 3 | New features: soft deletes, unique, tests | Phase 2 |
| 4 | CLI command: `make:crud` + dry-run + progress bar | Phase 2 |
| 5 | Livewire target stub set + LivewireGeneratorService methods | Phase 2 |
| 6 | Inertia target stub set (Vue + React) + Inertia controller gen | Phase 2 |
| 7 | Remaining features: relationships, events, bulk, print | Phase 3 |
| 8 | DX polish: checkFilesAreSame, interactive wizard | Phase 4 |

Phases can be parallelized:
- Phase 1 alone can be shipped as minor release
- Phase 2-4 (features + CLI) as next minor
- Phase 5-6 (new targets) as next minor
- Phase 7-8 as next minor

---

## 8. Phase 1 Implementation — Usage Documentation

Phase 1 implemented: target-aware stub resolution, target field in requests, soft deletes, unique validation, test scaffolding (Pest), `make:crud` CLI command, and file conflict detection.

### 8.1 Stub Architecture (✅ Done)

**Stub directory layout after Phase 1:**

```
stubs/
├── blade/
│   ├── controllers/         ← controller-*, single-form-controller-*, controller-api-*
│   ├── views/               ← create, edit, index, show, form, action, simple/*
│   └── route.stub
├── shared/
│   ├── api/                 ← resource, resource-collection
│   ├── model.stub
│   ├── migration.stub
│   ├── seeder.stub
│   ├── factory.stub
│   ├── request.stub
│   ├── resource.stub
│   ├── resource-collection.stub
│   ├── export.stub
│   ├── sidebar-dynamic.stub
│   ├── sidebar-static.stub
│   ├── view-composer.stub
│   ├── model-cast.stub
│   ├── user-role-api.stub
│   └── test.stub            ← NEW: Pest test skeleton
├── publish/                 ← unchanged
├── api.stub                 ← still at root (not yet relocated)
└── models/                  ← still at root (cast-image.stub)
```

**Stub resolution order** (implemented in `GeneratorUtils::getStub()`):

| Priority | Path | Example |
|----------|------|---------|
| 1 | `stubs/{target}/{name}.stub` | `stubs/blade/controllers/controller.stub` |
| 2 | `stubs/shared/{name}.stub` | `stubs/shared/model.stub` |
| 3 | `stubs/shared/api/{name}.stub` | `stubs/shared/api/resource.stub` |
| 4 | `stubs/blade/{name}.stub` | `stubs/blade/route.stub` (fallback) |

Signature: `getStub(string $name, ?string $target = null): string` — `$target` defaults to `request('target', 'blade')`.

**Token processing:** `replaceStub()` auto-wraps bare token names with `{{ }}`. Pass `'modelName' => 'Product'` not `'{{ modelName }}' => 'Product'`.

**Internals:** CLI command sets `is_simple_generator: true`, which skips sidebar menu setup and permission generation. Generated modules are fully functional but won't appear in the sidebar — use the web UI for sidebar integration.

### 8.2 Target System (✅ Partial)

**Implemented:**
- `target` field accepted in `StoreGeneratorRequest` and `StoreSimpleGeneratorRequest`
- Validated values: `blade`, `livewire`, `inertia-vue`, `inertia-react`
- Default: `blade` (backward compatible)
- `getStub()` target-aware resolution in place

**Web form:** New **Target** select group added to both `simple-create.blade.php` and `include/form.blade.php`:
```
Target: [Blade (Mazer) | Livewire (Flux UI) | Inertia-Vue (shadcn-vue) | Inertia-React (shadcn/ui)]
```

**Not yet implemented (Phase 5-6):**
- Livewire/Inertia-specific controllers, views, and routes
- Target-specific generator service methods (`generateLivewire()`, `generateInertia()`)

### 8.3 make:crud CLI Command (✅ Done)

```bash
php artisan make:crud {model?} [options]
```

**Options:**

| Option | Default | Description |
|--------|---------|-------------|
| `--fields=` | `name:string:required` | Comma-separated field definitions |
| `--target=` | `blade` | Output target |
| `--variant=` | `default` | Variant: `default`, `api`, `single-form` |
| `--soft-deletes` | — | Add `SoftDeletes` trait + `softDeletes()` column |
| `--tests` | — | Generate Pest feature tests |
| `--events` | — | Generate event classes |
| `--dry-run` | — | Preview without writing files |

**Field syntax:** `name:type:modifiers`
```
--fields="title,email:string:required:unique,body:text,qty:integer:required"
```

Modifiers: `required`, `unique`. Type defaults to `string`.

**Input type** inferred from column type:

| Column Type | Input Type |
|-------------|-----------|
| `text` | `textarea` |
| `boolean` | `select` |
| `date`, `dateTime` | `date` |
| `decimal`, `double`, `float`, `integer` | `number` |
| others | `text` |

**Quick examples:**

```bash
# Basic
php artisan make:crud Product

# With fields + soft deletes + tests
php artisan make:crud Post --fields=title,body:text:required --soft-deletes --tests

# Unique email + dry-run preview
php artisan make:crud User --fields=email:string:required:unique --dry-run

# API variant
php artisan make:crud Product --variant=api
```

**API variant:**
```bash
php artisan make:crud Product --variant=api
```
Differences: controller returns JSON, API resource routes, no Blade views, `is_simple_generator: true`.

**Note:** `--events` option is accepted but event scaffolding not yet implemented (no-op).

**Interactive mode** (run without model argument):
```
What is the model name? (e.g. Product)
> Product

Output target:
  [0] blade
  [1] livewire
  [2] inertia-vue
  [3] inertia-react
> 0

Generator variant:
  [0] default
  [1] single-form
  [2] api
> 0

Define your fields. Leave name empty to finish.
Field name (or empty to finish)
> title
Type for 'title':
> string
Field name (or empty to finish)
>
```

### 8.4 Soft Deletes (✅ Done)

**Web form checkbox** (in Add-ons section):
```
☐ Soft Deletes
  Add soft deletes to the model and migration
```

**What it does:**
- Adds `use Illuminate\Database\Eloquent\SoftDeletes;` import to generated model
- Adds `use SoftDeletes;` trait inside model class
- Adds `$table->softDeletes();` column to generated migration

**Generated model (basic — no soft deletes):**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = ['name'];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
        ];
    }
}
```

**Generated model (with soft deletes):**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'posts';

    protected $fillable = ['title', 'slug', 'body'];

    protected function casts(): array
    {
        return [
            'title' => 'string',
            'slug' => 'string',
            'body' => 'string',
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
        ];
    }
}
```

**Generated migration (with soft deletes):**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(table: 'posts', callback: function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->string('slug');
            $table->text('body');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(table: 'posts');
    }
};
```

**Validation rule:** `'soft_delete' => ['nullable', 'in:1,0,true,false']`

**CLI:** `php artisan make:crud Product --soft-deletes`

**Not yet implemented (future phases):**
- Controller: index/show `withTrashed()` option
- Restore action and route
- Force delete UI

### 8.5 Unique Validation (✅ Done)

**Per-field checkbox** in table fields grid:

| # | Field name | Column Type | Length | Input Type | Required | **Unique** | Action |
|---|------------|-------------|--------|------------|----------|------------|--------|

Checking Unique on a field adds `'unique:table_name,field_name'` to the generated form request's validation rules.

**Example:** Generated `StorePostRequest.php` with `slug` field marked unique:
```php
<?php

namespace App\Http\Requests\Posts;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'unique:posts,slug'],
            'body' => ['required', 'string'],
        ];
    }
}
```

**Validation rule:** `'uniques.*' => ['nullable', 'in:1,0,on']`

**CLI:** `php artisan make:crud User --fields=email:string:required:unique`

### 8.6 Test Scaffolding (Pest) (✅ Done)

**Web form checkbox:**
```
☐ Generate Tests
  Generate Pest feature tests
```

**Generated file:** `tests/Feature/{ModelPlural}/{Model}Test.php`

**Generated test file (`tests/Feature/Posts/PostTest.php`):**
```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Posts;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

it('can list posts', function () {
    $post = Post::factory()->create();

    $response = $this->get('/posts');

    $response->assertOk();
});

it('can create a post', function () {
    $response = $this->post('/posts', [
        'title' => 'Test Post',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post',
    ]);
});

it('validates required fields', function () {
    $response = $this->post('/posts', []);

    $response->assertSessionHasErrors(['title']);
});

it('can show a post', function () {
    $post = Post::factory()->create();

    $response = $this->get('/posts/'.$post->id);

    $response->assertOk();
});

it('can update a post', function () {
    $post = Post::factory()->create();

    $response = $this->put('/posts/'.$post->id, [
        'title' => 'Updated Post',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Updated Post',
    ]);
});

it('can delete a post', function () {
    $post = Post::factory()->create();

    $response = $this->delete('/posts/'.$post->id);

    $response->assertOk();
    $this->assertModelMissing($post);
});
```

**Stub:** `stubs/shared/test.stub` with tokens: `{{ modelName }}`, `{{ modelPluralPascal }}`, `{{ modelPluralKebab }}`, `{{ modelSingularCamel }}`, `{{ modelPluralSnake }}`, `{{ fields.0.name }}`, `{{ modelSingularKebab }}`

**CLI:** `php artisan make:crud Product --tests`

**Not yet implemented:**
- Only Blade/Inertia targets (Livewire test approach differs — Phase 5)

### 8.7 File Conflict Detection (✅ Done)

**Activated** existing `checkFilesAreSame()` in `GeneratorService.php`. Runs before generation in `SimpleGeneratorController::store()`.

**Behavior:** If model, migration, or controller file with the same name already exists, returns `403` with conflict details:
```json
[
    { "model": "The Product model already exists" },
    { "migration": "The Product migration already exists" },
    { "controller": "The ProductController already exists" }
]
```

### 8.8 Complete Generated Module Example

Running `php artisan make:crud Post --fields=title,slug:string:required:unique,body:text:required --soft-deletes --tests` produces these files:

**Generated Controller (`app/Http/Controllers/PostController.php`):**
```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\Posts\{StorePostRequest, UpdatePostRequest};
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};

class PostController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // new Middleware(middleware: 'permission:post view', only: ['index', 'show']),
            // new Middleware(middleware: 'permission:post create', only: ['create', 'store']),
            // new Middleware(middleware: 'permission:post edit', only: ['edit', 'update']),
            // new Middleware(middleware: 'permission:post delete', only: ['destroy']),
        ];
    }

    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $posts = Post::query();

            return DataTables::of(source: $posts)
                ->addColumn(name: 'body', content: fn($row): ?string => str(string: $row->body)->limit(100))
                ->addColumn(name: 'action', content: 'posts.include.action')
                ->toJson();
        }

        return view(view: 'posts.index');
    }

    public function create(): View
    {
        return view(view: 'posts.create');
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        Post::create(attributes: $request->validated());

        return to_route(route: 'posts.index')->with(key: 'success', value: __(key: 'The post was created successfully.'));
    }

    public function show(Post $post): View
    {
        return view(view: 'posts.show', data: compact(var_name: 'post'));
    }

    public function edit(Post $post): View
    {
        return view(view: 'posts.edit', data: compact(var_name: 'post'));
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->update(attributes: $request->validated());

        return to_route(route: 'posts.index')->with(key: 'success', value: __(key: 'The post was updated successfully.'));
    }

    public function destroy(Post $post): RedirectResponse
    {
        try {
            $post->delete();

            return to_route(route: 'posts.index')->with(key: 'success', value: __(key: 'The post was deleted successfully.'));
        } catch (\Exception $e) {
            return to_route(route: 'posts.index')->with(key: 'error', value: __(key: "The post can't be deleted because it's related to another table."));
        }
    }
}
```

**Complete file set generated for `Post`:**
```
app/Models/Post.php                              ← model + SoftDeletes trait
app/Http/Controllers/PostController.php          ← CRUD controller
app/Http/Requests/Posts/StorePostRequest.php     ← validation + unique:posts,slug
app/Http/Requests/Posts/UpdatePostRequest.php    ← update validation
database/migrations/2026_06_30_000000_create_posts_table.php  ← migration + softDeletes()
resources/views/posts/index.blade.php            ← DataTable index view
resources/views/posts/create.blade.php           ← create form
resources/views/posts/edit.blade.php             ← edit form
resources/views/posts/show.blade.php             ← detail view
resources/views/posts/include/action.blade.php   ← action buttons partial
resources/views/posts/include/form.blade.php     ← form partial
tests/Feature/Posts/PostTest.php                 ← 6 Pest tests
```

### 8.9 Deviations from Original Design

| Design Spec | Implementation | Reason |
|-------------|---------------|--------|
| `stubs/generators/` subdirectory | Flat `stubs/` (blade/, shared/) | Avoid breaking existing published stubs; the subdirectory was unnecessary overhead |
| `getStub()` with `stubs/generators/` prefix | `stubs/` prefix (no `generators/` subdir) | Same as above — backward compat |
| Feature toggles as pure stub variables | Soft deletes uses PHP in generators to set replacement values | More testable and explicit; stub variables alone would require complex conditional logic |
| Controller `withTrashed()` for soft deletes | Not implemented | Out of scope for Phase 1 — deferred to later phase |
| Edit request excludes current record for uniques | Not implemented | Requires route model binding access from stub — deferred |
| `{{ uniqueRules }}` in request.stub | Inline `unique:table,field` added per-field in `buildFieldValidation()` | Cleaner implementation; avoids separate stub token processing |
