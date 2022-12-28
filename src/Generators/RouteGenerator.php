<?php

namespace Zzzul\Generator\Generators;

use Illuminate\Support\Facades\File;

class RouteGenerator
{
    /**
     * Generate a route on web.php.
     *
     * @param array $request
     * @return void
     */
    public function generate(array $request)
    {
        $model = GeneratorUtils::setModelName($request['model']);
        $path = GeneratorUtils::getModelLocation($request['model']);

        $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase($model);
        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase($model);

        $middleware = "->middleware('auth')";
        
        if(isset($request['is_simple_generator'])){
            $middleware = "";
        }

        if ($path != '') {
            $controllerClass = "\n" . "Route::resource('" . $modelNamePluralKebabCase . "', App\Http\Controllers\\" . str_replace('/', '\\', $path) . "\\" . $modelNameSingularPascalCase . "Controller::class)$middleware;";
        } else {
            $controllerClass = "\n" . "Route::resource('" . $modelNamePluralKebabCase . "', App\Http\Controllers\\" . $modelNameSingularPascalCase . "Controller::class)$middleware;";
        }

        File::append(base_path('routes/web.php'), $controllerClass);
    }
}
