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

