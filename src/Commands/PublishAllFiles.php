<?php

namespace EvdigiIna\Generator\Commands;

use EvdigiIna\Generator\Generators\GeneratorUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class PublishAllFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:install {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all required files for the generator package';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $type = $this->argument(key: 'type');

        if (!in_array(needle: $type, haystack: ['full', 'simple'])) {
            $this->error(string: "Invalid type. Please specify either 'full' for the complete version or 'simple' for basic functionality.");

            return;
        }

        if ($type === 'full') {
            $this->handleFullInstallation();
        } else {
            $this->handleSimpleInstallation();
        }
    }

    /**
     * Handle full version installation.
     */
    protected function handleFullInstallation(): void
    {
        if (!$this->verifyPackageRequirements()) {
            return;
        }

        $runCount = $this->getRunCount(type: 'full_version_publish_count');

        if ($runCount >= 1) {
            if (!$this->confirm(question: 'The full version is already installed. Continue anyway?')) {
                return;
            }

            if (
                !$this->confirm(question: sprintf(
                    'You\'ve run this command %d time(s) before. Proceed with installation?',
                    $runCount
                ))
            ) {
                return;
            }
        }

        if (!$this->confirm(question: 'This will publish all files and may overwrite existing ones. Continue?')) {
            return;
        }

        $this->incrementRunCount(type: 'full_version_publish_count');
        $this->executeFullInstallation();
    }

    /**
     * Handle simple version installation.
     */
    protected function handleSimpleInstallation(): void
    {
        $runCount = $this->getRunCount(type: 'simple_version_publish_count');
        $fullRunCount = $this->getRunCount(type: 'full_version_publish_count');

        if ($fullRunCount >= 1) {
            $this->info(string: 'Note: The full version includes all simple version features. No additional installation needed.');

            return;
        }

        if ($runCount >= 1) {
            $this->info(string: sprintf(
                format: 'The simple version was already installed %d time(s). No changes made.',
                values: $runCount
            ));

            return;
        }

        $this->incrementRunCount(type: 'simple_version_publish_count');

        $this->info(string: 'Starting simple version installation...');
        $this->info(string: 'This may take a few moments. Please wait...');

        $this->executeWithProgress(commands: [
            'vendor:publish --tag=generator-config-simple',
            'vendor:publish --tag=generator-simple-provider',
            'vendor:publish --provider="Intervention\Image\Laravel\ServiceProvider"',
            'vendor:publish --tag=datatables',
            'vendor:publish --tag=generator-utils',
            'vendor:publish --tag=generator-bootstrap-app-simple',
        ]);

        $this->info(string: 'Simple version installed successfully!');
    }

    /**
     * Verify required packages are installed.
     */
    protected function verifyPackageRequirements(): bool
    {
        // $composerContent = file_get_contents(filename: base_path(path: 'composer.json'));
        $missing = [];

        if (!class_exists(class: "Laravel\Fortify\Fortify")) {
            $missing[] = 'laravel/fortify';
        }

        if (!class_exists(class: "Spatie\Permission\PermissionServiceProvider")) {
            $missing[] = 'spatie/laravel-permission';
        }

        if (!empty($missing)) {
            $this->error(string: 'Required packages missing:');
            $this->error(string: implode(', ', $missing));
            $this->line(string: 'Please install them first using:');
            $this->line(string: 'composer require ' . implode(' ', $missing));

            return false;
        }

        return true;
    }

    /**
     * Execute commands with progress feedback.
     */
    protected function executeWithProgress(array $commands): void
    {
        $bar = $this->output->createProgressBar(max: count(value: $commands));
        $bar->setFormat(format: ' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage(message: 'Starting...');
        $bar->start();

        foreach ($commands as $command) {
            $bar->setMessage(message: "Running: {$command}");
            Artisan::call(command: $command);
            $bar->advance();
            usleep(microseconds: 200000); // 0.2s delay for smoother progress
        }

        $bar->setMessage(message: 'Complete!');
        $bar->finish();
        $this->newLine();
    }

    /**
     * Get the run count for a specific type.
     */
    protected function getRunCount(string $type): int
    {
        $counts = $this->getRunCounts();

        return $counts[$type] ?? 0;
    }

    /**
     * Increment the run count for a specific type.
     */
    protected function incrementRunCount(string $type): void
    {
        $counts = $this->getRunCounts();
        $counts[$type] = ($counts[$type] ?? 0) + 1;
        $this->saveRunCounts(counts: $counts);
    }

    /**
     * Get all run counts from storage.
     */
    protected function getRunCounts(): array
    {
        $file = storage_path(path: 'generator.cache');

        if (!file_exists(filename: $file)) {
            return [
                'simple_version_publish_count' => 0,
                'full_version_publish_count' => 0,
            ];
        }

        return json_decode(json: file_get_contents(filename: $file), associative: true) ?? [];
    }

    /**
     * Save run counts to storage.
     */
    protected function saveRunCounts(array $counts): void
    {
        file_put_contents(
            filename: storage_path(path: 'generator.cache'),
            data: json_encode(value: $counts)
        );
    }

    /**
     * Execute full version installation.
     */
    protected function executeFullInstallation(): void
    {
        $this->info(string: 'Beginning full version installation...');
        $this->info(string: 'This process may take a few minutes. Thank you for your patience.');

        $commands = [
            'vendor:publish --tag=generator-views --force',
            'vendor:publish --tag=generator-full-config --force',
            'vendor:publish --tag=generator-controller --force',
            'vendor:publish --tag=generator-request-user --force',
            'vendor:publish --tag=generator-request-role --force',
            'vendor:publish --tag=generator-action --force',
            'vendor:publish --tag=generator-full-provider --force',
            'vendor:publish --tag=generator-migration --force',
            'vendor:publish --tag=generator-seeder --force',
            'vendor:publish --tag=generator-model --force',
            'vendor:publish --tag=generator-assets --force',
            'vendor:publish --tag=generator-utils --force',
            'vendor:publish --tag=datatables --force',
            'vendor:publish --tag=generator-bootstrap-app-full --force',
            'vendor:publish --provider="Intervention\Image\Laravel\ServiceProvider" --force',
        ];

        $this->executeWithProgress(commands: $commands);

        // Append routes
        $template = GeneratorUtils::getStub(path: 'route');
        File::append(path: base_path(path: 'routes/web.php'), data: $template);

        $this->info(string: 'Full version installed successfully!');
        $this->line(string: 'Thank you for using our generator package!');
    }
}
