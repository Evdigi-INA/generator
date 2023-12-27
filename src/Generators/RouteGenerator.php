<?php

namespace EvdigiIna\Generator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

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

        if (isset($request['is_simple_generator']) || GeneratorUtils::isGenerateApi()) {
            $middleware = "";
        }

        if (GeneratorUtils::isGenerateApi()) {
            $routeFacade = "Route::apiResource('";
        } else {
            $routeFacade = "Route::resource('";
        }

        if ($path != '') {
            if (GeneratorUtils::isGenerateApi()) {
                $controllerClass = "\n" . $routeFacade . $modelNamePluralKebabCase . "', App\Http\Controllers\Api\\";
            } else {
                $controllerClass = "\n" . $routeFacade . $modelNamePluralKebabCase . "', App\Http\Controllers\\";
            }

            $controllerClass .= str_replace('/', '\\', $path) . "\\";
        } else {
            if (GeneratorUtils::isGenerateApi()) {
                $controllerClass = "\n" . $routeFacade . $modelNamePluralKebabCase . "', App\Http\Controllers\Api\\";
            } else {
                $controllerClass = "\n" . $routeFacade . $modelNamePluralKebabCase . "', App\Http\Controllers\\";
            }
        }

        Log::info('is_api_route', [GeneratorUtils::isGenerateApi()]);

        $controllerClass .=  $modelNameSingularPascalCase . "Controller::class)$middleware;";

        File::append(base_path(GeneratorUtils::isGenerateApi() ? 'routes/api.php' : 'routes/web.php'), $controllerClass);
    }
}
