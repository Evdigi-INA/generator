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
    protected $signature = 'generator:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all the required files, like views, config, assets, etc.';

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
        $this->info('Publishing the required file for you...');

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

        $this->info('All the required files were published successfully.');
    }
}
