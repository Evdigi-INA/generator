<?php

namespace Zzzul\Generator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

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
                $this->info('Publishing all the required files..');

                Artisan::call('vendor:publish --tag=generator-view --force');
                Artisan::call('vendor:publish --tag=generator-config --force');
                Artisan::call('vendor:publish --tag=generator-route --force');
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
                
                $this->info('All the required files were published successfully.');
            break;
            case 'simple':
                Artisan::call('vendor:publish --tag=generator-config');
                Artisan::call('vendor:publish --tag=generator-view-provider');
                Artisan::call('vendor:publish --provider="Intervention\Image\ImageServiceProviderLaravelRecent"');
                Artisan::call('vendor:publish --tag=datatables --force');

                $this->info('All the required files was published successfully.');
            break;
            default:
                $this->error("The type must be 'all' or 'simple'!");
            break;
        }
    }
}
