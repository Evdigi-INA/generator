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

