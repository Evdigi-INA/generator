# Task 4: Unique Validation Feature

## Status: DONE

### Commits
- `2c12a8a` — `feat: add unique validation per field`

### Test Results
- **77 tests, 97 assertions** — all passing

### Changes
1. **`src/Generators/RequestGenerator.php`**: Added `addUniqueValidation()` method that appends `'unique:table,field'` rule when `$request['uniques'][$index]` is truthy. Wired it into `buildFieldValidation()` after `addLengthValidations()`.
2. **`src/Http/Requests/StoreGeneratorRequest.php`**: Added `'uniques.*' => ['nullable', 'in:1,0,on']` validation rule.
3. **`src/Http/Requests/StoreSimpleGeneratorRequest.php`**: Same rule addition.
4. **`views/include/form.blade.php`**: Added `<th>Unique</th>` column header and checkbox `<td>` in field rows.
5. **`views/simple-create.blade.php`**: Same UI additions.
6. **`tests/GeneratorTest.php`**: Added `it_can_generate_module_with_unique_validation` test that posts `uniques => ['1']` for the email field and asserts 201 + unique rule in generated request content.

### Verification
- Generated request files contain `'unique:table_name,field_name'` for checked fields (confirmed via test)
- Pint formatting applied

### Concerns
None.
