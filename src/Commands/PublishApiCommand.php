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

        $this->info('Preparing to publish API files...');

        $commands = [
            'vendor:publish --tag=generator-request-api',
            'vendor:publish --tag=generator-controller-api',
            'vendor:publish --tag=generator-request-role',
            'vendor:publish --tag=generator-request-user',
        ];

        $template = GeneratorUtils::getStub('api');

        if ($this->confirm('Include user and role resources? (requires spatie/laravel-permission)', false)) {
            array_unshift($commands, 'vendor:publish --tag=generator-role-user-resource-api');

            $template .= "\n// Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::get('permissions', [App\Http\Controllers\API\RoleAndPermissionController::class, 'getAllPermissions']);
    Route::resource('users', App\Http\Controllers\API\UserController::class);
    Route::resource('roles', App\Http\Controllers\API\RoleAndPermissionController::class);
// });\n";
        }

        $this->executeWithProgress($commands);

        File::append(base_path('routes/api.php'), $template);

        $this->info('API files published successfully!');
        $this->line('API endpoints are now ready for use.');
    }

    /**
     * Verify API is properly installed before proceeding.
     */
    protected function verifyApiInstallation(): void
    {
        if (! (new GeneratorService)->apiRouteAlreadyExists()) {
            $this->error('API installation not detected.');
            $this->line('To use this feature, please first install the API by running:');
            $this->line('php artisan install:api');
            exit(1);
        }
    }

    /**
     * Execute commands with progress feedback.
     */
    protected function executeWithProgress(array $commands): void
    {
        $bar = $this->output->createProgressBar(count($commands));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('Initializing...');
        $bar->start();

        foreach ($commands as $command) {
            $bar->setMessage("Publishing: {$command}");
            Artisan::call($command);
            $bar->advance();
            usleep(200000); // 0.2s delay for smoother progress
        }

        $bar->setMessage('Finalizing...');
        $bar->finish();
        $this->newLine();
    }
}
