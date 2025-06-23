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
    protected $description = 'Publish all required API files including routes, controllers, and request validations';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->verifyApiInstallation();

        $this->info(string: 'Preparing to publish API files...');

        $commands = [
            'vendor:publish --tag=generator-controller-api',
        ];

        $template = GeneratorUtils::getStub(path: 'api');
        $isPublishUserRoles = false;

        if ($this->confirm(question: 'Include user and role resources? (requires spatie/laravel-permission)', default: false)) {
            $commands = array_merge($commands, [
                'vendor:publish --tag=generator-request-api',
                'vendor:publish --tag=generator-request-role',
                'vendor:publish --tag=generator-request-user',
                'vendor:publish --tag=generator-resource-api',
                'vendor:publish --tag=generator-controller-user-role-api'
            ]);

            $template .= GeneratorUtils::getStub(path: 'user-role-api');

            $isPublishUserRoles = true;
        }

        $this->executeWithProgress(commands: $commands);

        File::append(path: base_path(path: 'routes/api.php'), data: $template);

        $this->info(string: 'API files published successfully!');

        $this->newLine();

        $this->info(string: '[POST]: /api/auth/login, [Body]: {email: admin@example.com, password: password}');
        $this->info(string: '[POST]: /api/auth/register');

        if ($isPublishUserRoles) {
            $this->newLine();

            $this->info(string: '[GET]: /api/users');
            $this->info(string: '[POST]: /api/users');
            $this->info(string: '[GET]: /api/users/{id}');
            $this->info(string: '[PUT|PATCH]: /api/users/{id}');
            $this->info(string: '[DELETE]: /api/users/{id}');

            $this->newLine();

            $this->info(string: '[GET]: /api/roles');
            $this->info(string: '[POST]: /api/roles');
            $this->info(string: '[GET]: /api/roles/{id}');
            $this->info(string: '[PUT|PATCH]: /api/roles/{id}');
            $this->info(string: '[DELETE]: /api/roles/{id}');
        }

        $this->line(string: 'API endpoints are now ready for use.');
    }

    /**
     * Verify API is properly installed before proceeding.
     */
    protected function verifyApiInstallation(): void
    {
        if (!(new GeneratorService)->apiRouteAlreadyExists()) {
            $this->error(string: 'API installation not detected.');
            $this->line(string: 'To use this feature, please first install the API by running:');
            $this->line(string: 'php artisan install:api');
            exit(1);
        }
    }

    /**
     * Execute commands with progress feedback.
     */
    protected function executeWithProgress(array $commands): void
    {
        // Flatten the commands array in case any elements are arrays themselves
        $flatCommands = [];
        foreach ($commands as $command) {
            if (is_array(value: $command)) {
                $flatCommands = array_merge($flatCommands, $command);
            } else {
                $flatCommands[] = $command;
            }
        }

        $bar = $this->output->createProgressBar(max: count(value: $flatCommands));
        $bar->setFormat(format: ' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage(message: 'Initializing...');
        $bar->start();

        foreach ($flatCommands as $command) {
            $bar->setMessage(message: "Publishing: {$command}");
            Artisan::call(command: $command);
            $bar->advance();
            usleep(microseconds: 200000);
        }

        $bar->setMessage(message: 'Finalizing...');
        $bar->finish();
        $this->newLine();
    }
}
