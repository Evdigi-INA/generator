<?php

namespace EvdigiIna\Generator\Generators;

use EvdigiIna\Generator\Enums\GeneratorVariant;
use Illuminate\Support\Facades\File;

class RouteGenerator
{
    /**
     * Generate a route on web.php.
     */
    public function generate(array $request): void
    {
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);

        $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase($model);
        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase($model);

        $middleware = GeneratorUtils::isGenerateApi() || isset($request['is_simple_generator']) || GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value
            ? ""
            : "->middleware('auth')";

        $routeFacade = GeneratorUtils::isGenerateApi() ? "Route::apiResource('" : "Route::resource('";
        $controllerPath = '';

        switch ($path) {
            case true:
                switch (GeneratorUtils::isGenerateApi()) {
                    case true:
                        $controllerClass = "\n" . $routeFacade . $modelNamePluralKebabCase . "', App\Http\Controllers\Api\\";
                        $controllerPath = "App\Http\Controllers\Api\\" . $path . $modelNameSingularPascalCase . "Controller::class";
                        break;
                    default:
                        $controllerPath = "App\Http\Controllers\\" . $path . $modelNameSingularPascalCase . "Controller::class";
                        $controllerClass = "\n" . $routeFacade . $modelNamePluralKebabCase . "', App\Http\Controllers\\";
                        break;
                }
                $controllerClass .= str_replace('/', '\\', $path) . "\\";
                break;
            default:
                switch (GeneratorUtils::isGenerateApi()) {
                    case true:
                        $controllerClass = "\n" . $routeFacade . $modelNamePluralKebabCase . "', App\Http\Controllers\Api\\";
                        $controllerPath = "App\Http\Controllers\Api\\" . $modelNameSingularPascalCase . "Controller::class";
                        break;
                    default:
                        $controllerPath = "App\Http\Controllers\\" . $modelNameSingularPascalCase . "Controller::class";
                        $controllerClass = "\n" . $routeFacade . $modelNamePluralKebabCase . "', App\Http\Controllers\\";
                        break;
                }
                break;
        }

        $controllerClass .= $modelNameSingularPascalCase . "Controller::class)" . $middleware;

        $controllerClass .= GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value ? "->only(['index', 'store']);" : ";";

        File::append(base_path(GeneratorUtils::isGenerateApi() ? 'routes/api.php' : 'routes/web.php'), $controllerClass);

        // if(isset($request['generate_export']) && $request['generate_export'] == 'yes') {
        $exportRoute = "\nRoute::get('/export/$modelNamePluralKebabCase', [$controllerPath, 'export'])->name('$modelNamePluralKebabCase.export');";

        File::append(base_path(GeneratorUtils::isGenerateApi() ? 'routes/api.php' : 'routes/web.php'), $exportRoute);
        // }
    }
}
