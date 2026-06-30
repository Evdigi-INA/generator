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
    protected $description = 'Publish the improved Image Service (v2) with enhanced functionality';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info(string: 'Preparing to publish Image Service v2...');

        $this->executeWithProgress(
            command: 'vendor:publish --tag=image-service-v2',
            message: 'Publishing Image Service files'
        );

        $this->info(string: 'Image Service v2 published successfully!');
        $this->line(string: 'The enhanced image processing service is now ready for use.');
    }

    /**
     * Execute command with progress feedback.
     */
    protected function executeWithProgress(string $command, string $message): void
    {
        $bar = $this->output->createProgressBar(max: 1);
        $bar->setFormat(format: " %message%\n %current%/%max% [%bar%] %percent:3s%%");
        $bar->setMessage(message: $message);
        $bar->start();

        Artisan::call(command: $command);
        $bar->advance();

        $bar->finish();
        $this->newLine();
    }
}
