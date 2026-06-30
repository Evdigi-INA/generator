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

