<?php

namespace EvdigiIna\Generator\Services;

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;
use EvdigiIna\Generator\Generators\{
    ControllerGenerator,
    MenuGenerator,
    ModelGenerator,
    MigrationGenerator,
    PermissionGenerator,
    RequestGenerator,
    RouteGenerator,
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

class GeneratorService
{
    /**
     * Generate all CRUD modules.
     *
     * @param array $request
     * @return void
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

        $this->checkSidebarType();
    }

    /**
     * Generate only model and migration.
     *
     * @param array $request
     * @return void
     */
    public function onlyGenerateModelAndMigration(array $request): void
    {
        (new ModelGenerator)->generate($request);

        (new MigrationGenerator)->generate($request);
    }

    /**
     * Simple generator, only generate the core module(CRUD).
     *
     * @param array $request
     * @return void
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
     *
     * @param int $index
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSidebarMenusByIndex(int $index): \Illuminate\Http\JsonResponse
    {
        abort_if(!request()->ajax(), Response::HTTP_FORBIDDEN);

        return response()->json(config('generator.sidebars')[$index], Response::HTTP_OK);
    }

    /**
     * Check sidebar view.
     *
     * @return void
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
     *
     * @param array $request
     * @return array
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
            file_exists(app_path("/Http/Controllers/${model}Controller.php")) ||
            file_exists(app_path("/Http/Controllers/$path") . "/${model}Controller.php")
        ) {
            $sameFile[] = [
                'controller' => "The ${model}Controller is already exists"
            ];
        }

        return $sameFile;
    }
}
