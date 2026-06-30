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

