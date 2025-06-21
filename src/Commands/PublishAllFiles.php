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
        $type = $this->argument('type');

        if (! in_array($type, ['full', 'simple'])) {
            $this->error("Invalid type. Please specify either 'full' for the complete version or 'simple' for basic functionality.");

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
        if (! $this->verifyPackageRequirements()) {
            return;
        }

        $runCount = $this->getRunCount('full_version_publish_count');

        if ($runCount >= 1) {
            if (! $this->confirm('The full version is already installed. Continue anyway?')) {
                return;
            }

            if (! $this->confirm(sprintf(
                'You\'ve run this command %d time(s) before. Proceed with installation?',
                $runCount
            ))) {
                return;
            }
        }

        if (! $this->confirm('This will publish all files and may overwrite existing ones. Continue?')) {
            return;
        }

        $this->incrementRunCount('full_version_publish_count');
        $this->executeFullInstallation();
    }

    /**
     * Handle simple version installation.
     */
    protected function handleSimpleInstallation(): void
    {
        $runCount = $this->getRunCount('simple_version_publish_count');
        $fullRunCount = $this->getRunCount('full_version_publish_count');

        if ($fullRunCount >= 1) {
            $this->info('Note: The full version includes all simple version features. No additional installation needed.');

            return;
        }

        if ($runCount >= 1) {
            $this->info(sprintf(
                'The simple version was already installed %d time(s). No changes made.',
                $runCount
            ));

            return;
        }

        $this->incrementRunCount('simple_version_publish_count');

        $this->info('Starting simple version installation...');
        $this->info('This may take a few moments. Please wait...');

        $this->executeWithProgress([
            'vendor:publish --tag=generator-config-simple',
            'vendor:publish --tag=generator-simple-provider',
            'vendor:publish --provider="Intervention\Image\Laravel\ServiceProvider"',
            'vendor:publish --tag=datatables',
            'vendor:publish --tag=generator-utils',
            'vendor:publish --tag=generator-bootstrap-app-simple',
        ]);

        $this->info('Simple version installed successfully!');
    }

    /**
     * Verify required packages are installed.
     */
    protected function verifyPackageRequirements(): bool
    {
        $composerContent = file_get_contents(base_path('composer.json'));
        $missing = [];

        if (! str_contains($composerContent, 'laravel/fortify')) {
            $missing[] = 'laravel/fortify';
        }

        if (! str_contains($composerContent, 'spatie/laravel-permission')) {
            $missing[] = 'spatie/laravel-permission';
        }

        if (! empty($missing)) {
            $this->error('Required packages missing:');
            $this->error(implode(', ', $missing));
            $this->line('Please install them first using:');
            $this->line('composer require '.implode(' ', $missing));

            return false;
        }

        return true;
    }

    /**
     * Execute commands with progress feedback.
     */
    protected function executeWithProgress(array $commands): void
    {
        $bar = $this->output->createProgressBar(count($commands));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('Starting...');
        $bar->start();

        foreach ($commands as $command) {
            $bar->setMessage("Running: {$command}");
            Artisan::call($command);
            $bar->advance();
            usleep(200000); // 0.2s delay for smoother progress
        }

        $bar->setMessage('Complete!');
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
        $this->saveRunCounts($counts);
    }

    /**
     * Get all run counts from storage.
     */
    protected function getRunCounts(): array
    {
        $file = storage_path('generator.cache');

        if (! file_exists($file)) {
            return [
                'simple_version_publish_count' => 0,
                'full_version_publish_count' => 0,
            ];
        }

        return json_decode(file_get_contents($file), true) ?? [];
    }

    /**
     * Save run counts to storage.
     */
    protected function saveRunCounts(array $counts): void
    {
        file_put_contents(
            storage_path('generator.cache'),
            json_encode($counts)
        );
    }

    /**
     * Execute full version installation.
     */
    protected function executeFullInstallation(): void
    {
        $this->info('Beginning full version installation...');
        $this->info('This process may take a few minutes. Thank you for your patience.');

        $commands = [
            'vendor:publish --tag=generator-views',
            'vendor:publish --tag=generator-full-config',
            'vendor:publish --tag=generator-controller',
            'vendor:publish --tag=generator-request-user',
            'vendor:publish --tag=generator-request-role',
            'vendor:publish --tag=generator-action',
            'vendor:publish --tag=generator-full-provider',
            'vendor:publish --tag=generator-migration',
            'vendor:publish --tag=generator-seeder',
            'vendor:publish --tag=generator-model',
            'vendor:publish --tag=generator-assets',
            'vendor:publish --tag=generator-utils',
            'vendor:publish --tag=datatables',
            'vendor:publish --tag=generator-bootstrap-app-full',
            'vendor:publish --provider="Intervention\Image\Laravel\ServiceProvider"',
        ];

        $this->executeWithProgress($commands);

        // Append routes
        $template = GeneratorUtils::getStub('route');
        File::append(base_path('routes/web.php'), $template);

        $this->info('Full version installed successfully!');
        $this->line('Thank you for using our generator package!');
    }
}
