<?php

namespace Zzzul\Generator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Zzzul\Generator\Generators\GeneratorUtils;

class PublishAllFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:publish {type}';

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
            case 'all':
                $composer = file_get_contents(base_path('composer.json'));

                switch ($composer) {
                    case !str_contains($composer, 'laravel/fortify') && !str_contains($composer, 'spatie/laravel-permission'):
                        $this->error('You must install laravel/fortify and spatie/laravel-permission before running this command.');

                        $this->info('Install the package: composer require laravel/fortify spatie/laravel-permission');
                        break;
                    case !str_contains($composer, 'laravel/fortify'):
                        $this->error('You must install laravel/fortify before running this command.');

                        $this->info('Install the package: composer require laravel/fortify');
                        break;
                    case !str_contains($composer, 'spatie/laravel-permission'):
                        $this->error('You must install spatie/laravel-permission before running this command.');

                        $this->info('Install the package: composer require spatie/laravel-permission');
                        break;
                    default:
                        if ($this->confirm('Do you wish to continue? This command may overwrite several files.')) {
                                
                            $totalRunningCommand = $this->totalRunningCommand();

                            if($totalRunningCommand == "1" || intval($totalRunningCommand) > 1){
                                if ($this->confirm('Do you wish to continue? you are already this command a few ago.')) {
                                    $this->runPublishAll();
                                    break;
                                }
                            }else{
                                $this->runPublishAll();
                            }
                        }
                        break;
                }
                break;
            case 'simple':
                $this->info('Publishing all the required files (simple version)...');

                Artisan::call('vendor:publish --tag=generator-config-simple');
                Artisan::call('vendor:publish --tag=generator-view-provider');
                Artisan::call('vendor:publish --provider="Intervention\Image\ImageServiceProviderLaravelRecent"');
                Artisan::call('vendor:publish --tag=datatables');

                $this->info('All the required files were published successfully.');
                break;
            default:
                $this->error("The type must be 'all' or 'simple'!");
                break;
        }
    }

    /**
     * Check total running of generator:publish all command.
     * 
     * @return string
     * */
    public function totalRunningCommand(): string
    {
        if(!file_exists(__DIR__ . '/../../generator-cache.json')){
            file_put_contents(__DIR__ . '/../../generator-cache.json', 
                json_encode([
                    'generator_publish_simple' => null,
                    'generator_publish_all' => null
                ])
            );
        }

        $cache = file_get_contents(__DIR__ . '/../../generator-cache.json');

        // will get "null" or "1"
        $totalRunningCommand = \Str::before(\Str::after($cache, '"generator_publish_all":'), '}');

        if($totalRunningCommand == "null"){
            file_put_contents(__DIR__ . '/../../generator-cache.json', 
                json_encode([
                    'generator_publish_simple' => null,
                    'generator_publish_all' => 1
                ])
            );
        }else{
            file_put_contents(__DIR__ . '/../../generator-cache.json', 
                json_encode([
                    'generator_publish_simple' => null,
                    'generator_publish_all' => intval($totalRunningCommand) + 1
                ])
            );
        }

        return $totalRunningCommand;
    }

    /**
     * Publish all the required file for full version.
     * 
     * @return void
     * */
    public function runPublishAll(): void 
    {
        $this->info('Publishing all the required files...');

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
                            
        $this->info('All of the required files were successfully published..');
    }
}
