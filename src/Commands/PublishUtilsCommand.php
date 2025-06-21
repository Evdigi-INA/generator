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
        $this->info('Preparing utility files...');

        $this->executeWithProgress(
            'vendor:publish --tag=generator-utils --force',
            'Publishing utility classes'
        );

        $this->info('Utility classes published successfully!');
        $this->line('The generator utilities are now ready for use.');
    }

    /**
     * Execute command with progress feedback.
     */
    protected function executeWithProgress(string $command, string $message): void
    {
        $bar = $this->output->createProgressBar(1);
        $bar->setFormat(" %message%\n %current%/%max% [%bar%] %percent:3s%%");
        $bar->setMessage($message);
        $bar->start();

        Artisan::call($command);
        $bar->advance();

        $bar->finish();
        $this->newLine();
    }
}
