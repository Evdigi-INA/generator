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
    protected $description = 'Publish essential utility classes for the generator package';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info(string: 'Preparing utility files...');

        $this->executeWithProgress(
            command: 'vendor:publish --tag=generator-utils',
            message: 'Publishing utility classes'
        );

        $this->info(string: 'Utility classes published successfully!');
        $this->line(string: 'The generator utilities are now ready for use.');
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
