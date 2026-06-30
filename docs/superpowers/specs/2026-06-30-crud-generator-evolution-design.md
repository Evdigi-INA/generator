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
