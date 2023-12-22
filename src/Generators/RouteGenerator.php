<?php

namespace EvdigiIna\Generator\Generators;

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

        $middleware = "->middleware('auth')";

        if (isset($request['is_simple_generator']) || isset($request['generate_variant']) && $request['generate_variant'] == 'api') {
            $middleware = "";
        }

        if (isset($request['generate_variant']) && $request['generate_variant'] == 'api') {
            $routeFacade = "Route::apiResource('";
        } else {
            $routeFacade = "Route::resource('";
        }

        if ($path != '') {
            $controllerClass = "\n" . $routeFacade . $modelNamePluralKebabCase . "', App\Http\Controllers\\" . str_replace('/', '\\', $path) . "\\" . $modelNameSingularPascalCase . "Controller::class)$middleware;";
        } else {
            $controllerClass = "\n" . $routeFacade . $modelNamePluralKebabCase . "', App\Http\Controllers\\" . $modelNameSingularPascalCase . "Controller::class)$middleware;";
        }

        File::append(base_path(isset($request['generate_variant']) && $request['generate_variant'] == 'api' ? 'routes/api.php' : 'routes/web.php'), $controllerClass);
    }
}
