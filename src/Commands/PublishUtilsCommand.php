<?php

namespace EvdigiIna\Generator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PublishUtilsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:publish-utils';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish utility class.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Artisan::call('vendor:publish --tag=generator-utils --force');

        $this->info('Utility class published successfully.');
    }
}
