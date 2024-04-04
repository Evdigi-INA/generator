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

        $controllerClass = match ($path) {
            true => "\n" . $routeFacade . $modelNamePluralKebabCase . "', App\Http\Controllers\\" . str_replace('/', '\\', $path) . "\\",
            default => "\n" . $routeFacade . $modelNamePluralKebabCase . "', App\Http\Controllers\\",
        };

        $controllerClass .= $modelNameSingularPascalCase . "Controller::class)" . $middleware;

        $controllerClass .= GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value ? "->only(['index', 'store']);" : ";";

        File::append(base_path(GeneratorUtils::isGenerateApi() ? 'routes/api.php' : 'routes/web.php'), $controllerClass);
    }
}
