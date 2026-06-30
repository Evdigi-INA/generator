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

