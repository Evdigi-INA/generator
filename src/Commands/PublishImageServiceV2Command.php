<?php

namespace EvdigiIna\Generator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PublishImageServiceV2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:publish-image-service-v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish new image service class.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Artisan::call('vendor:publish --tag=image-service-v2 --force');

        $this->info('New image service class published successfully.');
    }
}
