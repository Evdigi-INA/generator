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

