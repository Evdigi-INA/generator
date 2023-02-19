<?php

namespace EvdigiIna\Generator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use EvdigiIna\Generator\Generators\GeneratorUtils;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        switch ($this->argument('type')) {
            case 'full':
                $composer = file_get_contents(base_path('composer.json'));

                switch ($composer) {
                    case !str_contains($composer, 'laravel/fortify') && !str_contains($composer, 'spatie/laravel-permission'):
                        $this->error('You must be install laravel/fortify and spatie/laravel-permission before running this command.');

                        $this->info('Install the package: composer require laravel/fortify spatie/laravel-permission');
                        break;
                    case !str_contains($composer, 'laravel/fortify'):
                        $this->error('You must be install laravel/fortify before running this command.');

                        $this->info('Install the package: composer require laravel/fortify');
                        break;
                    case !str_contains($composer, 'spatie/laravel-permission'):
                        $this->error('You must be install spatie/laravel-permission before running this command.');

                        $this->info('Install the package: composer require spatie/laravel-permission');
                        break;
                    default:
                        $totalRunningCommand = $this->totalRunningCommand('generator_publish_all');

                        if (
                            $totalRunningCommand['generator_publish_simple'] == 1 || $totalRunningCommand['generator_publish_simple'] > 1
                        ) {
                            if (!$this->confirm('Do you wish to continue? You are already using the simple version.')) {
                                return;
                            }
                        }

                        if ($this->confirm('Do you wish to continue? This command may overwrite several files.')) {

                            if ($totalRunningCommand['generator_publish_all'] == 1 || $totalRunningCommand['generator_publish_all'] > 1) {

                                switch ($this->confirm('Do you wish to continue? you are already running this command ' . $totalRunningCommand['generator_publish_all'] . ' times.')) {
                                    case true:
                                        $this->runPublishAll();
                                        return;
                                        break;
                                    default:
                                        return;
                                        break;
                                }
                            }

                            $this->runPublishAll();
                        }

                        return;
                        break;
                }
                break;
            case 'simple':

                $totalRunningCommand = $this->totalRunningCommand('generator_publish_simple');

                if ($totalRunningCommand['generator_publish_all'] == 1 || $totalRunningCommand['generator_publish_all'] > 1) {
                    $this->info('You are using the full version, which already includes the simple version. So this command may not be affected.');

                    return;
                }

                if ($totalRunningCommand['generator_publish_simple'] == 1 || $totalRunningCommand['generator_publish_simple'] > 1) {
                    $this->info('You are already running this command ' . $totalRunningCommand['generator_publish_simple'] . ' times.');

                    return;
                }

                $this->info('Installing the simple version...');

                Artisan::call('vendor:publish --tag=generator-config-simple');
                Artisan::call('vendor:publish --tag=generator-view-provider');
                Artisan::call('vendor:publish --provider="Intervention\Image\ImageServiceProviderLaravelRecent"');
                Artisan::call('vendor:publish --tag=datatables');

                $this->info('Installed successfully.');
                break;
            default:
                $this->error("The type must be 'full' to use the full version or 'simple' to use the simple version.");
                break;
        }
    }

    /**
     * Check total running of generator:publish all command.
     *
     * @return array
     * */
    public function totalRunningCommand(string $type = 'generator_publish_all'): array
    {
        $dir = __DIR__ . '/../../generator-cache.json';

        if (!file_exists($dir)) {
            file_put_contents(
                $dir,
                json_encode([
                    'generator_publish_simple' => null,
                    'generator_publish_all' => null
                ])
            );
        }

        $cache = file_get_contents($dir);

        $totalRunningCommand = collect(json_decode($cache))->toArray();

        switch ($type) {
            case 'generator_publish_all':
                if ($totalRunningCommand['generator_publish_all'] == null) {
                    file_put_contents(
                        $dir,
                        json_encode([
                            'generator_publish_simple' => $totalRunningCommand['generator_publish_simple'],
                            'generator_publish_all' => 1
                        ])
                    );
                } else {
                    file_put_contents(
                        $dir,
                        json_encode([
                            'generator_publish_simple' => $totalRunningCommand['generator_publish_simple'],
                            'generator_publish_all' => $totalRunningCommand['generator_publish_all'] + 1
                        ])
                    );
                }
                break;
            default:
                if ($totalRunningCommand['generator_publish_simple'] == null) {
                    file_put_contents(
                        $dir,
                        json_encode([
                            'generator_publish_simple' => 1,
                            'generator_publish_all' => $totalRunningCommand['generator_publish_all']
                        ])
                    );
                } else {
                    file_put_contents(
                        $dir,
                        json_encode([
                            'generator_publish_simple' => $totalRunningCommand['generator_publish_simple'] + 1,
                            'generator_publish_all' => $totalRunningCommand['generator_publish_all']
                        ])
                    );
                }
                break;
        }

        return $totalRunningCommand;
    }

    /**
     * Publish all files required by the full version.
     *
     * @return void
     * */
    public function runPublishAll(): void
    {
        $this->info('Installing...');

        Artisan::call('vendor:publish --tag=generator-view --force');
        Artisan::call('vendor:publish --tag=generator-config --force');
        Artisan::call('vendor:publish --tag=generator-controller --force');
        Artisan::call('vendor:publish --tag=generator-request --force');
        Artisan::call('vendor:publish --tag=generator-action --force');
        Artisan::call('vendor:publish --tag=generator-kernel --force');
        Artisan::call('vendor:publish --tag=generator-provider --force');
        Artisan::call('vendor:publish --tag=generator-migration --force');
        Artisan::call('vendor:publish --tag=generator-seeder --force');
        Artisan::call('vendor:publish --tag=generator-model --force');
        Artisan::call('vendor:publish --tag=generator-assets --force');
        Artisan::call('vendor:publish --provider="Intervention\Image\ImageServiceProviderLaravelRecent"');
        Artisan::call('vendor:publish --tag=datatables --force');

        $template = GeneratorUtils::getTemplate('route');

        \File::append(base_path('routes/web.php'), $template);

        $this->info('Installed successfully.');
    }
}
