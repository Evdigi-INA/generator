<?php

namespace EvdigiIna\Generator\Generators\Services;

use EvdigiIna\Generator\Generators\Interfaces\GeneratorServiceInterface;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;
use EvdigiIna\Generator\Generators\{
    ControllerGenerator,
    FactoryGenerator,
    GeneratorUtils,
    MenuGenerator,
    ModelGenerator,
    MigrationGenerator,
    PermissionGenerator,
    RequestGenerator,
    RouteGenerator,
    SeederGenerator,
    ViewComposerGenerator
};
use EvdigiIna\Generator\Generators\Views\{
    ActionViewGenerator,
    CreateViewGenerator,
    EditViewGenerator,
    FormViewGenerator,
    IndexViewGenerator,
    ShowViewGenerator,
};

class GeneratorService implements GeneratorServiceInterface
{
    /**
     * Generate all CRUD modules.
     */
    public function generateAll(array $request): void
    {
        (new ModelGenerator)->generate($request);
        (new MigrationGenerator)->generate($request);
        (new ControllerGenerator)->generate($request);
        (new RequestGenerator)->generate($request);

        (new IndexViewGenerator)->generate($request);
        (new CreateViewGenerator)->generate($request);
        (new ShowViewGenerator)->generate($request);
        (new EditViewGenerator)->generate($request);
        (new ActionViewGenerator)->generate($request);
        (new FormViewGenerator)->generate($request);

        (new MenuGenerator)->generate($request);
        (new RouteGenerator)->generate($request);
        (new PermissionGenerator)->generate($request);

        if (in_array('foreignId', $request['column_types'])) {
            (new ViewComposerGenerator)->generate($request);
        }

        Artisan::call('migrate');

        if(isset($request['generate_seeder']) && $request['generate_seeder'] != null) {
            (new SeederGenerator)->generate($request);
        }

        if(isset($request['generate_factory']) && $request['generate_factory'] != null) {
            (new FactoryGenerator)->generate($request);
        }

        $this->checkSidebarType();
    }

    /**
     * Generate only model and migration.
     */
    public function onlyGenerateModelAndMigration(array $request): void
    {
        (new ModelGenerator)->generate($request);

        (new MigrationGenerator)->generate($request);
    }

    /**
     * Simple generator, only generate the core module(CRUD).
     */
    public function simpleGenerator(array $request): void
    {
        (new ModelGenerator)->generate($request);
        (new MigrationGenerator)->generate($request);
        (new ControllerGenerator)->generate($request);
        (new RequestGenerator)->generate($request);

        (new IndexViewGenerator)->generate($request);
        (new CreateViewGenerator)->generate($request);
        (new ShowViewGenerator)->generate($request);
        (new EditViewGenerator)->generate($request);
        (new ActionViewGenerator)->generate($request);
        (new FormViewGenerator)->generate($request);

        (new RouteGenerator)->generate($request);

        if (in_array('foreignId', $request['column_types'])) {
            (new ViewComposerGenerator)->generate($request);
        }

        Artisan::call('migrate');
    }


    /**
     * Get sidebar menus by index.
     */
    public function getSidebarMenusByIndex(int $index): array
    {
        abort_if(!request()->ajax(), Response::HTTP_FORBIDDEN);

        return config('generator.sidebars')[$index];
    }

    /**
     * Check sidebar view.
     */
    public function checkSidebarType(): void
    {
        $sidebar = file_get_contents(resource_path('views/layouts/sidebar.blade.php'));

        /** if the sidebar is static, then must be regenerated to update new menus */
        if (!str($sidebar)->contains("\$permissions = empty(\$menu['permission'])")) {
            Artisan::call('generator:sidebar dynamic');
        }
    }

    /**
     * Check to see if any files are the same as the generated files. (will be used in the future)
     * */
    public function checkFilesAreSame(array $request): array
    {
        $sameFile = [];

        $path = GeneratorUtils::getModelLocation($request['model']);
        $model = GeneratorUtils::singularPascalCase(GeneratorUtils::setModelName($request['model']));

        if (
            file_exists(app_path("/Models/$model.php")) ||
            file_exists(app_path("/Models/$path") . "/$model.php")
        ) {
            $sameFile[] = [
                'model' => "The $model model is already exists"
            ];
        }

        $checkMigrationFile = array_diff(scandir(database_path('/migrations')), array('.', '..'));

        foreach ($checkMigrationFile as $file) {
            if (str($file)->contains(GeneratorUtils::pluralSnakeCase($model))) {
                $sameFile[] = [
                    'migration' => "The $model migration is already exists"
                ];
            }
        }

        if (
            file_exists(app_path("/Http/Controllers/{$model}Controller.php")) ||
            file_exists(app_path("/Http/Controllers/$path") . "/{$model}Controller.php")
        ) {
            $sameFile[] = [
                'controller' => "The {$model}Controller is already exists"
            ];
        }

        return $sameFile;
    }
}
