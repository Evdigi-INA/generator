<?php

namespace EvdigiIna\Generator\Generators\Services;

use EvdigiIna\Generator\Enums\GeneratorVariant;
use EvdigiIna\Generator\Exceptions\GeneratorRollbackException;
use EvdigiIna\Generator\Generators\ControllerGenerator;
use EvdigiIna\Generator\Generators\ExportExcelGenerator;
use EvdigiIna\Generator\Generators\FactoryGenerator;
use EvdigiIna\Generator\Generators\GeneratorUtils;
use EvdigiIna\Generator\Generators\Interfaces\GeneratorServiceInterface;
use EvdigiIna\Generator\Generators\MenuGenerator;
use EvdigiIna\Generator\Generators\MigrationGenerator;
use EvdigiIna\Generator\Generators\ModelGenerator;
use EvdigiIna\Generator\Generators\PermissionGenerator;
use EvdigiIna\Generator\Generators\RequestGenerator;
use EvdigiIna\Generator\Generators\ResourceApiGenerator;
use EvdigiIna\Generator\Generators\RouteGenerator;
use EvdigiIna\Generator\Generators\SeederGenerator;
use EvdigiIna\Generator\Generators\TestGenerator;
use EvdigiIna\Generator\Generators\ViewComposerGenerator;
use EvdigiIna\Generator\Generators\Views\ActionViewGenerator;
use EvdigiIna\Generator\Generators\Views\CreateViewGenerator;
use EvdigiIna\Generator\Generators\Views\EditViewGenerator;
use EvdigiIna\Generator\Generators\Views\FormViewGenerator;
use EvdigiIna\Generator\Generators\Views\IndexViewGenerator;
use EvdigiIna\Generator\Generators\Views\ShowViewGenerator;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GeneratorService implements GeneratorServiceInterface
{
    /**
     * Generate all CRUD modules(API/Blade).
     */
    public function generate(array $request): void
    {
        $request['target'] ??= 'blade';

        if (GeneratorUtils::isGenerateApi() && ! $this->apiRouteAlreadyExists()) {
            abort(code: Response::HTTP_FORBIDDEN, message: 'You have not yet installed the API, to use this feature, you must be running the artisan command: "php artisan install:api".');
        }

        $createdFiles = [];

        try {
            (new PermissionGenerator)->generate($request);
            (new ModelGenerator)->generate($request);
            $createdFiles = array_merge($createdFiles, $this->captureCreatedFiles('model', $request));

            (new MigrationGenerator)->generate($request);
            $createdFiles = array_merge($createdFiles, $this->captureCreatedFiles('migration', $request));

            (new ControllerGenerator)->generate($request);
            $createdFiles = array_merge($createdFiles, $this->captureCreatedFiles('controller', $request));

            (new RequestGenerator)->generate($request);
            $createdFiles = array_merge($createdFiles, $this->captureCreatedFiles('request', $request));

            // blade template
            if (($request['generate_variant'] ?? '') !== 'api') {
                // for single form
                (new CreateViewGenerator)->generate($request);
                (new FormViewGenerator)->generate($request);

                // for full CRUD
                if ($request['generate_variant'] === GeneratorVariant::DEFAULT->value) {
                    (new IndexViewGenerator)->generate($request);
                    (new ShowViewGenerator)->generate($request);
                    (new EditViewGenerator)->generate($request);
                    (new ActionViewGenerator)->generate($request);
                }

                (new MenuGenerator)->generate($request);
                (new ViewComposerGenerator)->generate($request);
            }

            (new RouteGenerator)->generate($request);
            (new SeederGenerator)->generate($request);
            (new FactoryGenerator)->generate($request);
            (new ResourceApiGenerator)->generate($request);

            if ((($request['generate_variant'] ?? '') !== 'api') && empty($request['is_simple_generator'])) {
                $this->checkSidebarType();
            }

            (new ExportExcelGenerator)->generate($request);
            (new TestGenerator)->generate($request);

            Artisan::call('migrate');
        } catch (Throwable $e) {
            $this->cleanupCreatedFiles($createdFiles);

            throw new GeneratorRollbackException(
                message: 'Generator failed and rolled back: '.$e->getMessage(),
                createdFiles: $createdFiles,
                previous: $e,
            );
        }
    }

    /**
     * Generate only model and migration.
     */
    public function onlyGenerateModelAndMigration(array $request): void
    {
        (new ModelGenerator)->generate($request);
        (new MigrationGenerator)->generate($request);
        (new SeederGenerator)->generate($request);
        (new FactoryGenerator)->generate($request);
    }

    /**
     * Get sidebar menus by index.
     */
    public function getSidebarMenusByIndex(int $index): array
    {
        abort_if(! request()->ajax(), Response::HTTP_FORBIDDEN);

        return config('generator.sidebars')[$index];
    }

    /**
     * Check sidebar view.
     */
    public function checkSidebarType(): void
    {
        $sidebar = file_get_contents(resource_path('views/layouts/sidebar.blade.php'));

        /** if the sidebar is static, then must be regenerated to update new menus */
        if (! str($sidebar)->contains("\$permissions = empty(\$menu['permission'])")) {
            Artisan::call('generator:sidebar dynamic');
        }
    }

    /**
     * Check if API route already exists. (laravel 11)
     */
    public function apiRouteAlreadyExists(): bool
    {
        $bootstrapApp = file_get_contents(base_path('/bootstrap/app.php'));

        $checkApiRoute = (bool) str_contains($bootstrapApp, 'api') && str_contains($bootstrapApp, 'api.php') && str_contains($bootstrapApp, 'api:');

        $composerJson = file_get_contents(base_path('/composer.json'));

        $checkLaravelSanctum = str_contains($composerJson, 'sanctum');

        return $checkApiRoute && file_exists(base_path('/routes/api.php')) && $checkLaravelSanctum;
    }

    /**
     * Check to see if any files are the same as the generated files. (will be used in the future)
     */
    public function checkFilesAreSame(array $request): array
    {
        $sameFile = [];

        $path = GeneratorUtils::getModelLocation($request['model']);
        $model = GeneratorUtils::singularPascalCase(GeneratorUtils::setModelName($request['model']));

        if (
            file_exists(app_path("/Models/$model.php")) ||
            file_exists(app_path("/Models/$path")."/{$model}.php")
        ) {
            $sameFile[] = [
                'model' => "The $model model is already exists",
            ];
        }

        $checkMigrationFile = array_diff(scandir(database_path('/migrations')), ['.', '..']);

        foreach ($checkMigrationFile as $file) {
            if (str($file)->contains(GeneratorUtils::pluralSnakeCase($model))) {
                $sameFile[] = [
                    'migration' => "The $model migration is already exists",
                ];
            }
        }

        if (
            file_exists(app_path("/Http/Controllers/{$model}Controller.php")) ||
            file_exists(app_path("/Http/Controllers/$path")."/{$model}Controller.php")
        ) {
            $sameFile[] = [
                'controller' => "The {$model}Controller is already exists",
            ];
        }

        return $sameFile;
    }

    /**
     * Get all column types.
     */
    public function columnTypes(): array
    {
        return [
            'string',
            'text',
            'char',
            'tinyText',
            'mediumText',
            'longText',
            'integer',
            'tinyInteger',
            'mediumInteger',
            'bigInteger',
            'decimal',
            'double',
            'float',
            'boolean',
            'date',
            'time',
            'year',
            'dateTime',
            'enum',
            'foreignId',
        ];
    }

    /**
     * Track created files for potential rollback.
     */
    private function captureCreatedFiles(string $type, array $request): array
    {
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);
        $files = [];

        switch ($type) {
            case 'model':
                $modelName = GeneratorUtils::singularPascalCase($model);
                if ($path) {
                    $files[] = app_path("/Models/$path/$modelName.php");
                } else {
                    $files[] = app_path("/Models/$modelName.php");
                }
                break;
            case 'migration':
                $tableName = GeneratorUtils::pluralSnakeCase($model);
                foreach (glob(database_path("/migrations/*_create_{$tableName}_table.php")) as $file) {
                    $files[] = $file;
                }
                break;
            case 'controller':
                $modelName = GeneratorUtils::singularPascalCase($model);
                if ($path) {
                    $dir = GeneratorUtils::isGenerateApi() ? app_path("/Http/Controllers/Api/$path/") : app_path("/Http/Controllers/$path/");
                    $files[] = "$dir{$modelName}Controller.php";
                } else {
                    $dir = GeneratorUtils::isGenerateApi() ? app_path('/Http/Controllers/Api/') : app_path('/Http/Controllers/');
                    $files[] = "{$dir}{$modelName}Controller.php";
                }
                break;
            case 'request':
                $modelPascal = GeneratorUtils::singularPascalCase($model);
                $modelPluralPascal = GeneratorUtils::pluralPascalCase($model);
                if ($path) {
                    $dir = app_path("/Http/Requests/$path/$modelPluralPascal");
                    $files[] = "$dir/Store{$modelPascal}Request.php";
                    $files[] = "$dir/Update{$modelPascal}Request.php";
                } else {
                    $dir = app_path("/Http/Requests/$modelPluralPascal");
                    $files[] = "$dir/Store{$modelPascal}Request.php";
                    $files[] = "$dir/Update{$modelPascal}Request.php";
                }
                break;
        }

        return $files;
    }

    /**
     * Clean up created files during rollback.
     */
    private function cleanupCreatedFiles(array $files): void
    {
        foreach ($files as $file) {
            if ($file && file_exists($file)) {
                unlink($file);
            }
        }
    }
}
