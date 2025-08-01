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
    protected $description = 'Publish the required file for the generator.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        switch ($this->argument('type')) {
            case 'full':
                $composerFileText = file_get_contents(base_path('composer.json'));

                if (! str_contains($composerFileText, 'laravel/fortify') && ! str_contains($composerFileText, 'spatie/laravel-permission')) {
                    $this->error('You must be install laravel/fortify and spatie/laravel-permission before running this command.');

                    $this->info('Install the package: composer require laravel/fortify spatie/laravel-permission');

                    return;
                }

                if (! str_contains($composerFileText, 'laravel/fortify')) {
                    $this->error('You must be install laravel/fortify before running this command.');

                    $this->info('Install the package: composer require laravel/fortify');

                    return;
                }

                if (! str_contains($composerFileText, 'spatie/laravel-permission')) {
                    $this->error('You must be install spatie/laravel-permission before running this command.');

                    $this->info('Install the package: composer require spatie/laravel-permission');

                    return;
                }

                $totalRunningCommand = $this->totalRunningCommand('full_version_publish_count');

                if (
                    $totalRunningCommand['full_version_publish_count'] == 1 || $totalRunningCommand['full_version_publish_count'] > 1
                ) {
                    if (! $this->confirm('Do you wish to continue? You are already using the full version.')) {
                        return;
                    }
                }

                if ($this->confirm('Do you wish to continue? This command may overwrite several files.')) {

                    if ($totalRunningCommand['full_version_publish_count'] == 1 || $totalRunningCommand['full_version_publish_count'] > 1) {

                        if ($this->confirm('Do you wish to continue? you are already running this command '.$totalRunningCommand['full_version_publish_count'].' for times.')) {
                            $this->totalRunningCommand('full_version_publish_count', true);
                            $this->runPublishAll();

                            return;
                        }
                    }

                    $this->totalRunningCommand('full_version_publish_count', true);
                    $this->runPublishAll();

                    return;
                }
                break;
            case 'simple':
                $totalRunningCommand = $this->totalRunningCommand('simple_version_publish_count');

                if ($totalRunningCommand['full_version_publish_count'] == 1 || $totalRunningCommand['full_version_publish_count'] > 1) {
                    $this->info('You are using the full version, which already includes the simple version. This command may not be affected.');

                    return;
                }

                if ($totalRunningCommand['simple_version_publish_count'] == 1 || $totalRunningCommand['simple_version_publish_count'] > 1) {
                    $this->info('You are already running this command '.$totalRunningCommand['simple_version_publish_count'].' times.');

                    return;
                }

                $this->totalRunningCommand('simple_version_publish_count', true);

                $this->info('Installing the simple version...');
                $this->info('Please wait a bit, this process may take several minutes.');

                // $this->info('Loading');

                Artisan::call('vendor:publish --tag=generator-config-simple --force');

                // $this->info('Loading.');

                Artisan::call('vendor:publish --tag=generator-model-simple --force');

                // $this->info('Loading..');

                Artisan::call('vendor:publish --tag=generator-view-provider --force');

                // $this->info('Loading...');

                Artisan::call('vendor:publish --provider="Intervention\Image\Laravel\ServiceProvider" --force');

                // $this->info('Loading....');

                Artisan::call('vendor:publish --tag=datatables --force');

                // $this->info('Loading.....');

                Artisan::call('vendor:publish --tag=generator-utils --force');

                // $this->info('Loading......');

                Artisan::call('vendor:publish --tag=bootstrap-app-simple --force');

                // $this->info('Loading......');

                $this->info('Installed successfully.');
                break;
            default:
                $this->error("The type must be 'full' to use the full version or 'simple' to use the simple version.");
                break;
        }
    }

    /**
     * Check total running of generator:publish all command.
     * */
    public function totalRunningCommand(string $type = 'full_version_publish_count', bool $increment = false): array
    {
        // $dir = __DIR__ . '/../../generator.cache';
        $dir = storage_path('generator.cache');

        if (! file_exists($dir)) {
            file_put_contents(
                $dir,
                json_encode([
                    'simple_version_publish_count' => 0,
                    'full_version_publish_count' => 0,
                ])
            );
        }

        $cache = file_get_contents($dir);

        $totalRunningCommand = json_decode($cache, true);

        switch ($type) {
            case 'full_version_publish_count':
                if ($totalRunningCommand['full_version_publish_count'] == 0) {
                    if ($increment) {
                        file_put_contents(
                            $dir,
                            json_encode([
                                'simple_version_publish_count' => $totalRunningCommand['simple_version_publish_count'],
                                'full_version_publish_count' => 1,
                            ])
                        );
                    }
                } else {
                    if ($increment) {
                        file_put_contents(
                            $dir,
                            json_encode([
                                'simple_version_publish_count' => $totalRunningCommand['simple_version_publish_count'],
                                'full_version_publish_count' => $totalRunningCommand['full_version_publish_count'] + 1,
                            ])
                        );
                    }
                }
                break;
            default:
                if ($totalRunningCommand['simple_version_publish_count'] == 0) {
                    if ($increment) {
                        file_put_contents(
                            $dir,
                            json_encode([
                                'simple_version_publish_count' => 1,
                                'full_version_publish_count' => $totalRunningCommand['full_version_publish_count'],
                            ])
                        );
                    }
                } else {
                    if ($increment) {
                        file_put_contents(
                            $dir,
                            json_encode([
                                'simple_version_publish_count' => $totalRunningCommand['simple_version_publish_count'] + 1,
                                'full_version_publish_count' => $totalRunningCommand['full_version_publish_count'],
                            ])
                        );
                    }
                }
                break;
        }

        return $totalRunningCommand;
    }

    /**
     * Publish all files required by the full version.
     * */
    public function runPublishAll(): void
    {
        $this->info('Installing...');
        $this->info('Please wait a bit, this process may take several minutes.');

        // $this->info('Loading.');

        Artisan::call('vendor:publish --tag=generator-view --force');

        // $this->info('Loading..');

        Artisan::call('vendor:publish --tag=generator-config --force');

        // $this->info('Loading...');

        Artisan::call('vendor:publish --tag=generator-controller --force');

        // $this->info('Loading....');

        Artisan::call('vendor:publish --tag=generator-request-user --force');

        // $this->info('Loading.....');

        Artisan::call('vendor:publish --tag=generator-request-role --force');

        // $this->info('Loading......');

        Artisan::call('vendor:publish --tag=generator-action --force');

        // $this->info('Loading.......');

        // Artisan::call('vendor:publish --tag=generator-kernel --force');
        Artisan::call('vendor:publish --tag=generator-provider --force');

        // $this->info('Loading........');

        Artisan::call('vendor:publish --tag=generator-migration --force');

        // $this->info('Loading.........');

        Artisan::call('vendor:publish --tag=generator-seeder --force');

        // $this->info('Loading..........');

        Artisan::call('vendor:publish --tag=generator-model --force');

        // $this->info('Loading...........');

        Artisan::call('vendor:publish --tag=generator-assets --force');

        // $this->info('Loading............');

        Artisan::call('vendor:publish --tag=generator-utils --force');

        // $this->info('Loading.............');

        Artisan::call('vendor:publish --tag=datatables --force');

        // $this->info('Loading..............');

        Artisan::call('vendor:publish --tag=bootstrap-app-full --force');

        // $this->info('Loading...............');

        Artisan::call('vendor:publish --provider="Intervention\Image\Laravel\ServiceProvider"');

        // $this->info('Loading................');

        $template = GeneratorUtils::getStub('route');

        File::append(base_path('routes/web.php'), $template);

        // $this->info('Loading.................');

        $this->info('Installed successfully.');
    }
}
