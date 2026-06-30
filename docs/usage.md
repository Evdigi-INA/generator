# Usage Guide — CRUD Generator

## make:crud CLI Command

Generate a complete CRUD module from the terminal without the web UI.

### Quick Start

```bash
php artisan make:crud Product
```

This generates a `Product` CRUD module with a single `name` field (string, text input, required, Blade target).

### Options

| Option | Default | Description |
|--------|---------|-------------|
| `--fields=` | `name:string:required` | Field definitions (see syntax below) |
| `--target=` | `blade` | Output target: `blade`, `livewire`, `inertia-vue`, `inertia-react` |
| `--variant=` | `default` | Generator variant: `default`, `api`, `single-form` |
| `--soft-deletes` | — | Add `SoftDeletes` trait + `softDeletes()` column |
| `--tests` | — | Generate Pest feature tests |
| `--events` | — | Generate event classes |
| `--dry-run` | — | Preview without writing files |

### Field Syntax

```
--fields=name,email:string:required:unique,qty:integer:required,description:text:required
```

Each field is `name:type:modifiers` separated by commas:

| Part | Required | Description |
|------|----------|-------------|
| `name` | ✅ | Field name (e.g. `title`, `email`) |
| `type` | — | Column type: `string`, `text`, `integer`, `boolean`, `date`, `decimal`, `foreignId`, etc. Defaults to `string`. |
| `modifiers` | — | Comma-separated flags: `required`, `unique` |

**Input type** is inferred from column type:
- `text` → `textarea`
- `boolean` → `select`
- `date`, `dateTime` → `date`
- `decimal`, `double`, `float`, `integer` → `number`
- Everything else → `text`

### Examples

**Basic model with two fields:**
```bash
php artisan make:crud Post --fields=title,body:text:required
```

**With soft deletes, tests, and unique email:**
```bash
php artisan make:crud User --fields=name,email:string:required:unique,password:string:required --soft-deletes --tests
```

**Dry-run to preview:**
```bash
php artisan make:crud Product --dry-run
```

**API variant with different target:**
```bash
php artisan make:crud Product --variant=api --target=livewire
```

### Interactive Mode

Run without arguments for an interactive prompt:

```bash
php artisan make:crud
```

Full walkthrough:
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
Module 'Product' generated successfully!
```

### API Variant

The `--variant=api` flag generates API-only output:

```bash
php artisan make:crud Product --variant=api
```

Differences from default variant:
- Controller extends API controller pattern (returns JSON, no views)
- Generates API resource routes instead of web routes
- No Blade views generated
- Uses API form request with different validation structure
- `is_simple_generator` set to `true` (no sidebar/permission setup)

**With target:** API variant works with any target, but views are skipped:
```bash
php artisan make:crud Product --variant=api --target=blade
```

### Notes

- **`--events`** option is accepted but event scaffolding is not yet implemented — no-op in current version
- **No `--fields` provided:** defaults to a single `name:string:required` field
- **CLI vs Web UI:** CLI sets `is_simple_generator: true` internally, which skips sidebar menu setup and permission generation. Generated modules from CLI are fully functional but won't appear in the sidebar — add them manually or use the web UI for sidebar integration

---

## Target System

The generator supports four output targets for generated views/controllers:

| Target | Starter Kit | UI Framework | Status |
|--------|-------------|--------------|--------|
| `blade` | — | Mazer Bootstrap | ✅ Active (default) |
| `livewire` | livewire-starter-kit | Flux UI + Tailwind | 🚧 Coming |
| `inertia-vue` | vue-starter-kit | shadcn-vue + Tailwind | 🚧 Coming |
| `inertia-react` | react-starter-kit | shadcn/ui + Tailwind | 🚧 Coming |

Set target via the web form dropdown or CLI:

```bash
php artisan make:crud Product --target=blade
```

**Stub resolution order** (for future targets):
1. `stubs/{target}/{name}.stub`
2. `stubs/shared/{name}.stub` (shared across all targets)
3. `stubs/shared/api/{name}.stub` (API-specific shared)
4. `stubs/blade/{name}.stub` (fallback)

---

## Web UI Features

### Form Add-ons Section

Both the **Simple Generator** (`/simple-generators`) and **Full Generator** forms have an **Add-ons** section with checkboxes for optional features:

#### Soft Deletes

```
☐ Soft Deletes
  Add soft deletes to the model and migration
```

Checking this:

- Adds `use Illuminate\Database\Eloquent\SoftDeletes;` import statement at the top of the generated model
- Adds `use SoftDeletes;` trait declaration inside the model class body
- Adds `$table->softDeletes();` column to the generated migration (nullable timestamp)

**Generated model without soft deletes (`Product`):**
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

**Generated model with soft deletes (`Post`):**
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

**Generated migration with soft deletes:**
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

**CLI equivalent:**
```bash
php artisan make:crud Product --soft-deletes
```

#### Generate Tests

```
☐ Generate Tests
  Generate Pest feature tests
```

Checking this creates `tests/Feature/{ModelPlural}/{Model}Test.php` with Pest tests:

```
tests/Feature/Posts/PostTest.php
```

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

**CLI equivalent:**
```bash
php artisan make:crud Product --tests
```

### Per-Field Unique Validation

The **Unique** column checkbox appears in the table fields grid for each row:

| # | Field name | Column Type | Length | Input Type | Required | **Unique** | Action |
|---|------------|-------------|--------|------------|----------|------------|--------|
| 1 | email      | string      | 255    | email      | ☑       | **☐**     | ...    |

Checking Unique on a field adds `'unique:table_name,field_name'` to the generated form request's validation rules.

**Example:** Generated `StorePostRequest.php` with `slug` marked unique:
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

**CLI equivalent:**
```bash
php artisan make:crud User --fields=email:string:required:unique
```

---

## Stub Customization

Stubs are template files that the generator fills with content. All stubs live under the `stubs/` directory.

### Stub Resolution

When a generator requests a stub, it resolves in this order:

```
stubs/{target}/{name}.stub      → target-specific (e.g. stubs/blade/controllers/controller.stub)
stubs/shared/{name}.stub        → shared across all targets (e.g. stubs/shared/model.stub)
stubs/shared/api/{name}.stub    → API-specific shared (e.g. stubs/shared/api/resource.stub)
stubs/blade/{name}.stub         → fallback (e.g. stubs/blade/route.stub)
```

### Available Tokens

| Token | Source Method | Example Value | Phase 1 |
|-------|--------------|---------------|---------|
| `{{ modelName }}` | `setModelName()` | `Product` | ✅ |
| `{{ modelPluralKebab }}` | `pluralKebabCase()` | `products` | ✅ |
| `{{ modelSingularCamel }}` | `singularCamelCase()` | `product` | ✅ |
| `{{ modelPluralPascal }}` | `pluralPascalCase()` | `Products` | ✅ |
| `{{ modelSingularPascal }}` | `singularPascalCase()` | `Product` | ✅ |
| `{{ modelSingularSnake }}` | `singularSnakeCase()` | `product` | ✅ |
| `{{ modelPluralSnake }}` | `pluralSnakeCase()` | `products` | ✅ |
| `{{ namespace }}` | computed | `App\Models` | ✅ |
| `{{ fields }}` | from request | `'name', 'slug'` | ✅ |
| `{{ casts }}` | from column types | `'is_active' => 'boolean'` | ✅ |
| `{{ relations }}` | from foreignIds | `belongsTo()` method | ✅ |
| `{{ hasSoftDelete }}` | from `soft_delete` | `use SoftDeletes;` | ✅ |
| `{{ importSoftDelete }}` | from `soft_delete` | `use Illuminate\Database\Eloquent\SoftDeletes;` | ✅ |
| `{{ softDeleteColumn }}` | from `soft_delete` | `$table->softDeletes();` | ✅ |
| `{{ uniqueRules }}` | from `uniques[]` | `'unique:products,slug'` | ✅ |
| `{{ protectedHidden }}` | from password fields | `protected $hidden = [...]` | ✅ |
| `{{ tableNamePluralLowercase }}` | from model | `products` | ✅ |
| `{{ castImages }}` | from file inputs | cast code for images | ✅ |
| `{{ importCastImage }}` | from file inputs | image service import | ✅ |

**Token processing:** `GeneratorUtils::replaceStub()` automatically wraps token names with `{{ }}`. When passing tokens, use the bare name: `'modelName' => 'Product'` not `'{{ modelName }}' => 'Product'`.

### Creating Custom Stubs

To customize generated code, edit the `.stub` file and use `{{ tokenName }}` placeholders:

```blade
// stubs/shared/model.stub (excerpt)
class {{ modelName }} extends Model
{
    use HasFactory;
    {{ hasSoftDelete }}
    protected $table = '{{ pluralSnakeCase }}';
```

Any valid PHP code works in stubs — the generator does simple string replacement.

---

## File Conflict Detection

The generator checks for existing files before generating. If a model, controller, or migration with the same name already exists, the request returns a `403` response listing the conflicting files:

```json
[
    { "model": "The Product model already exists" },
    { "controller": "The ProductController already exists" }
]
```

This prevents accidentally overwriting existing code. To regenerate, delete the conflicting files first or choose a different model name.
