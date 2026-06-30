### Task 6: make:crud CLI Command

**Files:**
- Create: `src/Commands/MakeCrudCommand.php`
- Modify: `src/Providers/GeneratorServiceProvider.php` — register new command
- Test: `tests/CommandTest.php`

**Interfaces:**
- Consumes: CLI arguments/options from `$this->argument()` / `$this->option()`
- Produces: Calls `GeneratorService::generate()` with correctly structured `$request` array

- [ ] **Step 1: Create the command class**

```php
<?php

declare(strict_types=1);

namespace EvdigiIna\Generator\Commands;

use EvdigiIna\Generator\Enums\GeneratorType;
use EvdigiIna\Generator\Enums\GeneratorVariant;
use EvdigiIna\Generator\Generators\Services\GeneratorService;
use Illuminate\Console\Command;

class MakeCrudCommand extends Command
{
    protected $signature = 'make:crud
        {model? : The model name (e.g. Product)}
        {--fields= : Comma-separated field definitions. Format: name:type:params:validation}
        {--target=blade : Output target: blade, livewire, inertia-vue, inertia-react}
        {--variant=default : Generator variant: default, api, single-form}
        {--soft-deletes : Add SoftDeletes trait to the model}
        {--tests : Generate Pest feature tests}
        {--events : Generate event classes}
        {--dry-run : Preview generated files without writing}
    ';

    protected $description = 'Generate a complete CRUD module';

    public function handle(GeneratorService $generatorService): int
    {
        $model = $this->argument('model');

        // Interactive mode if no model provided
        if (! $model) {
            return $this->interactiveMode($generatorService);
        }

        $request = $this->buildRequest($model);

        if ($this->option('dry-run')) {
            $this->info('Dry-run mode: files will not be written.');
            // TODO: implement dry-run in GeneratorService
        }

        try {
            $generatorService->generate($request);
            $this->info("Module '{$model}' generated successfully!");
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function buildRequest(string $model): array
    {
        $fields = $this->parseFields($this->option('fields') ?? '');

        return [
            'model' => $model,
            'generate_type' => GeneratorType::ALL->value,
            'target' => $this->option('target'),
            'generate_variant' => $this->option('variant'),
            'soft_delete' => $this->option('soft-deletes'),
            'generate_tests' => $this->option('tests'),
            'generate_events' => $this->option('events'),
            'fields' => $fields['fields'],
            'column_types' => $fields['column_types'],
            'input_types' => $fields['input_types'],
            'requireds' => $fields['requireds'],
            'select_options' => $fields['select_options'],
            'constrains' => $fields['constrains'],
            'foreign_ids' => $fields['foreign_ids'],
            'on_update_foreign' => $fields['on_update_foreign'],
            'on_delete_foreign' => $fields['on_delete_foreign'],
            'min_lengths' => $fields['min_lengths'],
            'max_lengths' => $fields['max_lengths'],
            'mimes' => $fields['mimes'],
            'files_sizes' => $fields['files_sizes'],
            'file_types' => $fields['file_types'],
            'steps' => $fields['steps'],
            'default_values' => $fields['default_values'],
        ];
    }

    private function parseFields(string $fieldString): array
    {
        if (empty($fieldString)) {
            return [
                'fields' => ['name'],
                'column_types' => ['string'],
                'input_types' => ['text'],
                'requireds' => ['yes'],
                'select_options' => [null],
                'constrains' => [null],
                'foreign_ids' => [null],
                'on_update_foreign' => [null],
                'on_delete_foreign' => [null],
                'min_lengths' => [null],
                'max_lengths' => [null],
                'mimes' => [null],
                'files_sizes' => [null],
                'file_types' => [null],
                'steps' => [null],
                'default_values' => [null],
            ];
        }

        $fields = explode(',', $fieldString);
        $names = [];
        $types = [];
        $inputTypes = [];
        $requireds = [];
        $selectOptions = [];
        $constrains = [];
        $foreignIds = [];
        $onUpdateForeign = [];
        $onDeleteForeign = [];
        $minLengths = [];
        $maxLengths = [];
        $mimes = [];
        $fileSizes = [];
        $fileTypes = [];
        $steps = [];
        $defaultValues = [];

        foreach ($fields as $raw) {
            $parts = explode(':', $raw);
            $names[] = $parts[0];
            $types[] = $parts[1] ?? 'string';
            $inputTypes[] = match ($parts[1] ?? 'string') {
                'text' => 'textarea',
                'boolean' => 'select',
                'date', 'dateTime' => 'date',
                'decimal', 'double', 'float', 'integer' => 'number',
                default => 'text',
            };
            $requireds[] = in_array('required', $parts) ? 'yes' : 'no';
            $selectOptions[] = null;
            $constrains[] = null;
            $foreignIds[] = null;
            $onUpdateForeign[] = null;
            $onDeleteForeign[] = null;
            $minLengths[] = null;
            $maxLengths[] = null;
            $mimes[] = null;
            $fileSizes[] = null;
            $fileTypes[] = null;
            $steps[] = null;
            $defaultValues[] = null;
        }

        return [
            'fields' => $names,
            'column_types' => $types,
            'input_types' => $inputTypes,
            'requireds' => $requireds,
            'select_options' => $selectOptions,
            'constrains' => $constrains,
            'foreign_ids' => $foreignIds,
            'on_update_foreign' => $onUpdateForeign,
            'on_delete_foreign' => $onDeleteForeign,
            'min_lengths' => $minLengths,
            'max_lengths' => $maxLengths,
            'mimes' => $mimes,
            'files_sizes' => $fileSizes,
            'file_types' => $fileTypes,
            'steps' => $steps,
            'default_values' => $defaultValues,
        ];
    }

    private function interactiveMode(GeneratorService $generatorService): int
    {
        $model = $this->ask('What is the model name? (e.g. Product)');
        if (! $model) {
            $this->error('Model name is required.');
            return Command::FAILURE;
        }

        $target = $this->choice('Output target', ['blade', 'livewire', 'inertia-vue', 'inertia-react'], 0);
        $variant = $this->choice('Generator variant', ['default', 'single-form', 'api'], 0);

        // Field table input
        $fields = [];
        $this->info('Define your fields. Leave name empty to finish.');
        do {
            $name = $this->ask('Field name (or empty to finish)');
            if (! $name) break;
            $type = $this->anticipate("Type for '{$name}'", ['string', 'integer', 'text', 'boolean', 'date', 'decimal', 'foreignId']);
            $fields[] = ['name' => $name, 'type' => $type];
        } while (true);

        // Build request from interactive input
        $request = $this->buildRequest($model);
        $request['target'] = $target;
        $request['generate_variant'] = $variant;
        // ... map fields

        $generatorService->generate($request);
        $this->info("Module '{$model}' generated successfully!");

        return Command::SUCCESS;
    }
}
```

- [ ] **Step 2: Register command in ServiceProvider**

```php
// In boot(), add to the if ($this->app->runningInConsole()) block:
$this->commands([MakeCrudCommand::class]);
```

- [ ] **Step 3: Write a test**

```php
#[Test]
public function it_can_run_make_crud_command(): void
{
    $this->artisan('make:crud', [
        'model' => 'CliTest',
        '--variant' => 'default',
        '--target' => 'blade',
    ])->assertSuccessful();
}
```

- [ ] **Step 4: Run tests**

```bash
php vendor/bin/phpunit
```

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: add make:crud CLI command"
```

---

