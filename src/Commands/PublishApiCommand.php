<?php

namespace EvdigiIna\Generator\Commands;

use EvdigiIna\Generator\Generators\GeneratorUtils;
use EvdigiIna\Generator\Generators\Services\GeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class PublishApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:publish-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish api files, like routes and auth controllers.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (! (new GeneratorService)->apiRouteAlreadyExists()) {
            $this->error("'You have not yet installed the API, to use this feature, you must be running the artisan command: \"php artisan install:api\"'");

            return;
        }

        Artisan::call('vendor:publish --tag=generator-request-api --force');
        Artisan::call('vendor:publish --tag=generator-controller-api --force');

        $template = GeneratorUtils::getStub('api');

        File::append(base_path('routes/api.php'), $template);

        $this->info('Published api files successfully.');
    }
}
